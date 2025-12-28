<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\CreditMemo;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogService;

class AccountsReceivableController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Customers Management
     */
    public function customers(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = Customer::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('tax_id', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Payment terms filter
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

        $customers = $query->orderBy('name')->get();
        
        // Apply receivables filter (calculated field)
        if ($request->has('receivables')) {
            $receivablesFilter = $request->receivables;
            $customers = $customers->filter(function($customer) use ($receivablesFilter) {
                $receivable = (float)($customer->total_receivable ?? 0);
                if ($receivablesFilter === 'zero') {
                    return $receivable == 0;
                } elseif ($receivablesFilter === 'low') {
                    return $receivable > 0 && $receivable <= 100000;
                } elseif ($receivablesFilter === 'medium') {
                    return $receivable > 100000 && $receivable <= 1000000;
                } elseif ($receivablesFilter === 'high') {
                    return $receivable > 1000000;
                }
                return true;
            });
        }

        $accounts = ChartOfAccount::where('type', 'Asset')->where('category', 'Current Asset')->active()->get();

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportCustomersPdf($customers, $request);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportCustomersExcel($customers, $request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getCustomersData($request);
        }

        // Paginate for view
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 20;
        $items = $customers->forPage($currentPage, $perPage)->values();
        $paginatedCustomers = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $customers->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('modules.accounting.accounts-receivable.customers', compact('customers', 'paginatedCustomers', 'accounts'));
    }

    /**
     * Get Customers Data (AJAX)
     */
    public function getCustomersData(Request $request)
    {
        try {
            $validated = $request->validate([
                'search' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive',
                'receivables' => 'nullable|string|in:zero,low,medium,high',
                'payment_terms' => 'nullable|string|in:0-15,16-30,31-60,60+',
                'q' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:5|max:100'
            ]);

            $query = Customer::query();

            $searchTerm = $validated['q'] ?? $validated['search'] ?? '';
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('customer_code', 'like', "%{$searchTerm}%")
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

            $allCustomers = $query->get();
            
            if (!empty($validated['receivables'])) {
                $receivablesFilter = $validated['receivables'];
                $allCustomers = $allCustomers->filter(function($customer) use ($receivablesFilter) {
                    $receivable = (float)($customer->total_receivable ?? 0);
                    if ($receivablesFilter === 'zero') {
                        return $receivable == 0;
                    } elseif ($receivablesFilter === 'low') {
                        return $receivable > 0 && $receivable <= 100000;
                    } elseif ($receivablesFilter === 'medium') {
                        return $receivable > 100000 && $receivable <= 1000000;
                    } elseif ($receivablesFilter === 'high') {
                        return $receivable > 1000000;
                    }
                    return true;
                });
            }
            
            $totalCustomers = $allCustomers->count();
            $activeCustomers = $allCustomers->where('is_active', true)->count();
            $totalReceivables = round($allCustomers->sum('total_receivable'), 2);
            
            // Calculate overdue (invoices past due date)
            $totalOverdue = 0;
            foreach ($allCustomers as $customer) {
                $overdueInvoices = $customer->invoices()
                    ->whereIn('status', ['Sent', 'Partially Paid'])
                    ->where('due_date', '<', now())
                    ->get();
                $totalOverdue += $overdueInvoices->sum('balance');
            }

            $formattedCustomers = $allCustomers->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'name' => $customer->name,
                    'contact_person' => $customer->contact_person ?? '-',
                    'email' => $customer->email ?? '-',
                    'phone' => $customer->phone ?? $customer->mobile ?? '-',
                    'receivables' => (float)($customer->total_receivable ?? 0),
                    'is_active' => $customer->is_active,
                    'credit_limit' => (float)($customer->credit_limit ?? 0),
                    'payment_terms' => $customer->payment_terms_days ?? 30,
                ];
            });

            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedCustomers->count();
            $paginatedCustomers = $formattedCustomers->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_customers' => $totalCustomers,
                    'active_customers' => $activeCustomers,
                    'total_receivables' => $totalReceivables,
                    'total_overdue' => round($totalOverdue, 2),
                    'count' => $totalEntries
                ],
                'customers' => $paginatedCustomers,
                'page' => $page,
                'per_page' => $perPage
            ]);
        } catch (\Exception $e) {
            \Log::error('Customers data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading customers data: ' . $e->getMessage(),
                'summary' => [
                    'total_customers' => 0,
                    'active_customers' => 0,
                    'total_receivables' => 0,
                    'total_overdue' => 0,
                    'count' => 0
                ],
                'customers' => []
            ], 500);
        }
    }

    public function storeCustomer(Request $request)
    {
        if ($request->has('generate_code')) {
            return response()->json(['code' => Customer::generateCode()]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        try {
            $customer = Customer::create([
                'customer_code' => $request->customer_code ?? Customer::generateCode(),
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
                'payment_terms' => $request->payment_terms ?? 'Net 30',
                'payment_terms_days' => $request->payment_terms_days ?? 30,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully! Customer code: ' . $customer->customer_code,
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Invoices Management
     */
    public function invoices(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = Invoice::with(['customer', 'items']);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportInvoicesPdf($query->get());
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportInvoicesExcel($query->get(), $request);
        }

        // Check if AJAX data request (legacy support - now uses separate route)
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getInvoicesData($request);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(20);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $accounts = ChartOfAccount::where('type', 'Income')->active()->get();

        return view('modules.accounting.accounts-receivable.invoices', compact('invoices', 'customers', 'accounts'));
    }

    /**
     * Get Invoices Data (AJAX)
     */
    public function getInvoicesData(Request $request)
    {
        try {
            // Only get filter parameters, ignore form submission fields
            $filterData = [];
            $filterKeys = ['customer_id', 'status', 'date_from', 'date_to', 'q', 'page', 'per_page'];
            
            foreach ($filterKeys as $key) {
                $value = $request->input($key);
                // Convert empty strings to null
                $filterData[$key] = ($value === '' || $value === null) ? null : $value;
            }
            
            // Validate only filter data
            $validator = \Validator::make($filterData, [
                'customer_id' => 'nullable|integer',
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

            $query = Invoice::with(['customer', 'items']);

            if (!empty($validated['customer_id'])) {
                $query->where('customer_id', $validated['customer_id']);
            }

            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('invoice_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('invoice_date', '<=', $validated['date_to']);
            }

            $searchTerm = $validated['q'] ?? '';
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('invoice_no', 'like', "%{$searchTerm}%")
                      ->orWhere('reference_no', 'like', "%{$searchTerm}%")
                      ->orWhereHas('customer', function($customerQuery) use ($searchTerm) {
                          $customerQuery->where('name', 'like', "%{$searchTerm}%")
                                       ->orWhere('customer_code', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Get all invoices for summary calculation
            $allInvoices = $query->get();
            
            // Calculate totals
            $totalInvoices = $allInvoices->count();
            $totalAmount = round($allInvoices->sum('total_amount'), 2);
            $totalPaid = round($allInvoices->sum('paid_amount'), 2);
            $totalBalance = round($allInvoices->sum('balance'), 2);
            $totalOverdue = round($allInvoices->filter(fn($inv) => $inv->isOverdue())->sum('balance'), 2);

            // Format invoices
            $formattedInvoices = $allInvoices->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'customer_name' => $invoice->customer->name ?? 'N/A',
                    'customer_id' => $invoice->customer_id,
                    'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                    'invoice_date_display' => $invoice->invoice_date->format('d M Y'),
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'due_date_display' => $invoice->due_date->format('d M Y'),
                    'is_overdue' => $invoice->isOverdue(),
                    'total_amount' => (float)$invoice->total_amount,
                    'paid_amount' => (float)$invoice->paid_amount,
                    'balance' => (float)$invoice->balance,
                    'status' => $invoice->status,
                    'reference_no' => $invoice->reference_no ?? '-',
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedInvoices->count();
            $paginatedInvoices = $formattedInvoices->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_invoices' => $totalInvoices,
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance,
                    'total_overdue' => $totalOverdue,
                    'count' => $totalEntries
                ],
                'invoices' => $paginatedInvoices,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading invoices data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading invoices: ' . $e->getMessage(),
                'invoices' => []
            ], 500);
        }
    }

    public function storeInvoice(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
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
            $discountAmount = $validated['discount_amount'] ?? $request->discount_amount ?? 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $itemTax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;
                $subtotal += $lineTotal;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $invoice = Invoice::create([
                'invoice_no' => Invoice::generateInvoiceNo(),
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'reference_no' => $request->reference_no ?? '',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount,
                'paid_amount' => 0,
                'status' => 'Pending for Approval',
                'notes' => $request->notes ?? '',
                'terms' => $request->terms ?? '',
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $itemTax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
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
            $customer = Customer::find($request->customer_id);
            
            // Get Accounts Receivable account (Asset - what customer owes us)
            $arAccount = $customer->account_id ?? ChartOfAccount::where('code', 'AR')->first()?->id;
            
            if (!$arAccount) {
                // Create default AR account if it doesn't exist
                $arAccount = ChartOfAccount::firstOrCreate(
                    ['code' => 'AR'],
                    [
                        'name' => 'Accounts Receivable',
                        'type' => 'Asset',
                        'category' => 'Current Asset',
                        'is_active' => true,
                    ]
                )->id;
            }
            
            // Get Revenue/Income account from invoice items or create default
            $revenueAccount = null;
            
            // Check if any invoice items have account_id specified
            foreach ($validated['items'] as $item) {
                if (!empty($item['account_id'])) {
                    $revenueAccount = $item['account_id'];
                    break; // Use first item's account
                }
            }
            
            if (!$revenueAccount) {
                // Find or create default Sales Revenue account
                $revenueAccount = ChartOfAccount::where('code', 'SALES')
                    ->orWhere('code', 'REVENUE')
                    ->orWhere('name', 'like', '%Sales Revenue%')
                    ->where('type', 'Income')
                    ->first()?->id;
                
                if (!$revenueAccount) {
                    $revenueAccount = ChartOfAccount::firstOrCreate(
                        ['code' => 'SALES'],
                        [
                            'name' => 'Sales Revenue',
                            'type' => 'Income',
                            'category' => 'Operating Revenue',
                            'is_active' => true,
                        ]
                    )->id;
                }
            }
            
            // Ensure both accounts exist before creating entries
            if (!$arAccount || !$revenueAccount) {
                throw new \Exception('Required Chart of Accounts not found. Please set up Accounts Receivable and Revenue accounts.');
            }
            
            // Debit: Accounts Receivable (increases asset - customer owes us)
            GeneralLedger::create([
                'account_id' => $arAccount,
                'transaction_date' => $invoice->invoice_date,
                'reference_type' => 'Invoice',
                'reference_id' => $invoice->id,
                'reference_no' => $invoice->invoice_no,
                'type' => 'Debit',
                'amount' => $totalAmount,
                'description' => "Invoice to {$customer->name}",
                'source' => 'Sales',
                'created_by' => Auth::id(),
            ]);
            
            // Credit: Revenue/Income Account (increases income - we earned revenue)
            GeneralLedger::create([
                'account_id' => $revenueAccount,
                'transaction_date' => $invoice->invoice_date,
                'reference_type' => 'Invoice',
                'reference_id' => $invoice->id,
                'reference_no' => $invoice->invoice_no,
                'type' => 'Credit',
                'amount' => $totalAmount,
                'description' => "Sales revenue from invoice {$invoice->invoice_no}",
                'source' => 'Sales',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            // Log activity
            ActivityLogService::logCreated($invoice, "Created invoice {$invoice->invoice_no} for TZS " . number_format($invoice->total_amount, 2), [
                'invoice_no' => $invoice->invoice_no,
                'customer_id' => $invoice->customer_id,
                'customer_name' => $invoice->customer->name ?? 'N/A',
                'total_amount' => $invoice->total_amount,
                'invoice_date' => $invoice->invoice_date,
            ]);

            // Send success notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notify(
                Auth::id(),
                "Invoice '{$invoice->invoice_no}' has been created successfully. Amount: TZS " . number_format($invoice->total_amount, 2),
                route('modules.accounting.ar.invoices', ['invoice_id' => $invoice->id])
            );

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice' => $invoice->load('items', 'customer')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Invoice Payments
     */
    public function invoicePayments(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = InvoicePayment::with(['invoice.customer', 'bankAccount']);

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportPaymentsPdf($query->get());
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportPaymentsExcel($query->get(), $request);
        }

        // Check if AJAX data request (legacy support - now uses separate route)
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getPaymentsData($request);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);
        // Get invoices that are not fully paid (have balance > 0) and are approved/sent
        $invoices = Invoice::where('balance', '>', 0)
            ->whereIn('status', ['Sent', 'Partially Paid', 'Overdue', 'Approved'])
            ->with('customer')
            ->orderBy('invoice_date', 'desc')
            ->get();
        $bankAccounts = \App\Models\BankAccount::all();

        return view('modules.accounting.accounts-receivable.payments', compact('payments', 'invoices', 'bankAccounts'));
    }

    /**
     * Get Payments Data (AJAX)
     */
    public function getPaymentsData(Request $request)
    {
        try {
            // Only get filter parameters, ignore form submission fields
            $filterData = [];
            $filterKeys = ['invoice_id', 'payment_method', 'date_from', 'date_to', 'q', 'page', 'per_page'];
            
            foreach ($filterKeys as $key) {
                $value = $request->input($key);
                // Convert empty strings to null
                $filterData[$key] = ($value === '' || $value === null) ? null : $value;
            }
            
            // Validate only filter data
            $validator = \Validator::make($filterData, [
                'invoice_id' => 'nullable|integer',
                'payment_method' => 'nullable|string',
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

            $query = InvoicePayment::with(['invoice.customer', 'bankAccount']);

            if (!empty($validated['invoice_id'])) {
                $query->where('invoice_id', $validated['invoice_id']);
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

            $searchTerm = $validated['q'] ?? '';
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('payment_no', 'like', "%{$searchTerm}%")
                      ->orWhere('reference_no', 'like', "%{$searchTerm}%")
                      ->orWhereHas('invoice', function($invoiceQuery) use ($searchTerm) {
                          $invoiceQuery->where('invoice_no', 'like', "%{$searchTerm}%")
                                       ->orWhereHas('customer', function($customerQuery) use ($searchTerm) {
                                           $customerQuery->where('name', 'like', "%{$searchTerm}%")
                                                        ->orWhere('customer_code', 'like', "%{$searchTerm}%");
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
                return $payment->payment_date && 
                       $payment->payment_date->format('Y-m') == now()->format('Y-m');
            })->sum('amount'), 2);

            // Format payments
            $formattedPayments = $allPayments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'payment_no' => $payment->payment_no,
                    'invoice_no' => $payment->invoice->invoice_no ?? 'N/A',
                    'customer_name' => $payment->invoice->customer->name ?? 'N/A',
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '',
                    'payment_date_display' => $payment->payment_date ? $payment->payment_date->format('d M Y') : '',
                    'amount' => (float)$payment->amount,
                    'payment_method' => $payment->payment_method,
                    'reference_no' => $payment->reference_no ?? '-',
                    'bank_account' => $payment->bankAccount->name ?? '-',
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

    public function storeInvoicePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
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

            $invoice = Invoice::findOrFail($validated['invoice_id']);

            if ($validated['amount'] > $invoice->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds invoice balance'
                ], 400);
            }

            $payment = InvoicePayment::create([
                'payment_no' => InvoicePayment::generatePaymentNo(),
                'invoice_id' => $validated['invoice_id'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Update invoice
            $invoice->paid_amount += $validated['amount'];
            $invoice->balance = $invoice->total_amount - $invoice->paid_amount;
            $invoice->updateStatus();
            $invoice->save();

            // Post to General Ledger - Double Entry Bookkeeping
            $customer = $invoice->customer;
            
            // Get Accounts Receivable account (Asset - what customers owe us)
            $arAccount = $customer->account_id ?? ChartOfAccount::where('code', 'AR')->first()?->id;
            
            if (!$arAccount) {
                // Create default AR account if it doesn't exist
                $arAccount = ChartOfAccount::firstOrCreate(
                    ['code' => 'AR'],
                    [
                        'name' => 'Accounts Receivable',
                        'type' => 'Asset',
                        'category' => 'Current Asset',
                        'is_active' => true,
                    ]
                )->id;
            }
            
            // Get Cash/Bank account (Asset - what we're receiving into)
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
            if (!$arAccount || !$cashBankAccount) {
                throw new \Exception('Required Chart of Accounts not found. Please set up Accounts Receivable and Cash/Bank accounts.');
            }
            
            // Debit: Cash/Bank Account (increases our cash/bank balance)
            GeneralLedger::create([
                'account_id' => $cashBankAccount,
                'transaction_date' => $payment->payment_date,
                'reference_type' => 'InvoicePayment',
                'reference_id' => $payment->id,
                'reference_no' => $payment->payment_no,
                'type' => 'Debit',
                'amount' => $validated['amount'],
                'description' => "Payment for invoice {$invoice->invoice_no}",
                'source' => 'Payment',
                'created_by' => Auth::id(),
            ]);
            
            // Credit: Accounts Receivable (reduces what customer owes us)
            GeneralLedger::create([
                'account_id' => $arAccount,
                'transaction_date' => $payment->payment_date,
                'reference_type' => 'InvoicePayment',
                'reference_id' => $payment->id,
                'reference_no' => $payment->payment_no,
                'type' => 'Credit',
                'amount' => $validated['amount'],
                'description' => "Payment for invoice {$invoice->invoice_no}",
                'source' => 'Payment',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            // Log activity
            ActivityLogService::logAction('invoice_payment', "Processed payment of TZS " . number_format($validated['amount'], 2) . " for invoice {$invoice->invoice_no}", $payment, [
                'invoice_no' => $invoice->invoice_no,
                'payment_no' => $payment->payment_no,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'invoice_balance' => $invoice->balance,
            ]);

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
     * Export Payments PDF
     */
    private function exportPaymentsPdf($payments)
    {
        try {
            $data = [
                'payments' => $payments,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.accounting.pdf.payments', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Payments_List_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Payments PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Payments Excel
     */
    private function exportPaymentsExcel($payments, $request)
    {
        try {
            $filename = 'Payments_List_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($payments) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Payment No', 'Date', 'Invoice No', 'Customer', 
                    'Amount', 'Payment Method', 'Reference No'
                ]);

                // Data rows
                foreach ($payments as $payment) {
                    fputcsv($file, [
                        $payment->payment_no ?? '',
                        $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') : '',
                        $payment->invoice->invoice_no ?? 'N/A',
                        $payment->invoice->customer->name ?? 'N/A',
                        number_format($payment->amount ?? 0, 2),
                        $payment->payment_method ?? '',
                        $payment->reference_no ?? '',
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

    /**
     * Credit Memos
     */
    public function creditMemos(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = CreditMemo::with(['customer', 'invoice']);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('memo_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('memo_date', '<=', $request->date_to);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportCreditMemosPdf($query->get());
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportCreditMemosExcel($query->get(), $request);
        }

        // Check if AJAX data request (legacy support - now uses separate route)
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getCreditMemosData($request);
        }

        $creditMemos = $query->orderBy('memo_date', 'desc')->paginate(20);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $invoices = Invoice::whereIn('status', ['Sent', 'Partially Paid', 'Paid', 'Overdue'])->with('customer')->get();

        return view('modules.accounting.accounts-receivable.credit-memos', compact('creditMemos', 'customers', 'invoices'));
    }

    /**
     * Get Credit Memos Data (AJAX)
     */
    public function getCreditMemosData(Request $request)
    {
        try {
            // Only get filter parameters, ignore form submission fields
            $filterData = [];
            $filterKeys = ['customer_id', 'status', 'type', 'date_from', 'date_to', 'q', 'page', 'per_page'];
            
            foreach ($filterKeys as $key) {
                $value = $request->input($key);
                // Convert empty strings to null
                $filterData[$key] = ($value === '' || $value === null) ? null : $value;
            }
            
            // Validate only filter data
            $validator = \Validator::make($filterData, [
                'customer_id' => 'nullable|integer',
                'status' => 'nullable|string',
                'type' => 'nullable|string',
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

            $query = CreditMemo::with(['customer', 'invoice']);

            if (!empty($validated['customer_id'])) {
                $query->where('customer_id', $validated['customer_id']);
            }

            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (!empty($validated['type'])) {
                $query->where('type', $validated['type']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('memo_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('memo_date', '<=', $validated['date_to']);
            }

            $searchTerm = $validated['q'] ?? '';
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('memo_no', 'like', "%{$searchTerm}%")
                      ->orWhere('reason', 'like', "%{$searchTerm}%")
                      ->orWhereHas('customer', function($customerQuery) use ($searchTerm) {
                          $customerQuery->where('name', 'like', "%{$searchTerm}%")
                                       ->orWhere('customer_code', 'like', "%{$searchTerm}%");
                      })
                      ->orWhereHas('invoice', function($invoiceQuery) use ($searchTerm) {
                          $invoiceQuery->where('invoice_no', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Get all credit memos for summary calculation
            $allCreditMemos = $query->get();
            
            // Calculate totals
            $totalMemos = $allCreditMemos->count();
            $totalAmount = round($allCreditMemos->sum('amount'), 2);
            $draftAmount = round($allCreditMemos->where('status', 'Draft')->sum('amount'), 2);
            $postedAmount = round($allCreditMemos->where('status', 'Posted')->sum('amount'), 2);

            // Format credit memos
            $formattedMemos = $allCreditMemos->map(function($memo) {
                return [
                    'id' => $memo->id,
                    'memo_no' => $memo->memo_no,
                    'customer_name' => $memo->customer->name ?? 'N/A',
                    'customer_id' => $memo->customer_id,
                    'invoice_no' => $memo->invoice->invoice_no ?? '-',
                    'memo_date' => $memo->memo_date->format('Y-m-d'),
                    'memo_date_display' => $memo->memo_date->format('d M Y'),
                    'type' => $memo->type,
                    'amount' => (float)$memo->amount,
                    'reason' => $memo->reason ?? '-',
                    'status' => $memo->status,
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedMemos->count();
            $paginatedMemos = $formattedMemos->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_memos' => $totalMemos,
                    'total_amount' => $totalAmount,
                    'draft_amount' => $draftAmount,
                    'posted_amount' => $postedAmount,
                    'count' => $totalEntries
                ],
                'credit_memos' => $paginatedMemos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading credit memos data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading credit memos: ' . $e->getMessage(),
                'credit_memos' => []
            ], 500);
        }
    }

    public function storeCreditMemo(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'memo_date' => 'required|date',
                'type' => 'required|in:Return,Discount,Adjustment,Write-off',
                'amount' => 'required|numeric|min:0.01',
                'invoice_id' => 'nullable|exists:invoices,id',
                'reason' => 'nullable|string',
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

            $creditMemo = CreditMemo::create([
                'memo_no' => CreditMemo::generateMemoNo(),
                'invoice_id' => $validated['invoice_id'] ?? null,
                'customer_id' => $validated['customer_id'],
                'memo_date' => $validated['memo_date'],
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'reason' => $validated['reason'] ?? null,
                'status' => 'Pending for Approval',
                'created_by' => Auth::id(),
            ]);

            // If linked to invoice, update invoice
            if (!empty($validated['invoice_id'])) {
                $invoice = Invoice::find($validated['invoice_id']);
                if ($invoice) {
                    $invoice->balance = max(0, $invoice->balance - $validated['amount']);
                    $invoice->updateStatus();
                    $invoice->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Credit memo created successfully',
                'creditMemo' => $creditMemo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating credit memo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating credit memo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Credit Memos PDF
     */
    private function exportCreditMemosPdf($creditMemos)
    {
        try {
            $data = [
                'creditMemos' => $creditMemos,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.accounting.pdf.credit-memos', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Credit_Memos_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Credit Memos PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Credit Memos Excel
     */
    private function exportCreditMemosExcel($creditMemos, $request)
    {
        try {
            $filename = 'Credit_Memos_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($creditMemos) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Memo No', 'Date', 'Customer', 'Invoice No', 
                    'Type', 'Amount', 'Status', 'Reason'
                ]);

                // Data rows
                foreach ($creditMemos as $memo) {
                    fputcsv($file, [
                        $memo->memo_no ?? '',
                        $memo->memo_date ? \Carbon\Carbon::parse($memo->memo_date)->format('Y-m-d') : '',
                        $memo->customer->name ?? 'N/A',
                        $memo->invoice->invoice_no ?? '-',
                        $memo->type ?? '',
                        number_format($memo->amount ?? 0, 2),
                        $memo->status ?? '',
                        $memo->reason ?? '',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Credit Memos Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * A/R Aging Report
     */
    public function agingReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $asOfDate = $request->date ?? now()->format('Y-m-d');

        $invoices = Invoice::with('customer')
            ->whereIn('status', ['Sent', 'Partially Paid', 'Overdue'])
            ->where('balance', '>', 0)
            ->get()
            ->map(function($invoice) use ($asOfDate) {
                $daysPastDue = now()->diffInDays($invoice->due_date);
                $aging = [
                    'current' => $daysPastDue <= 0 ? $invoice->balance : 0,
                    '0-30' => $daysPastDue > 0 && $daysPastDue <= 30 ? $invoice->balance : 0,
                    '31-60' => $daysPastDue > 31 && $daysPastDue <= 60 ? $invoice->balance : 0,
                    '61-90' => $daysPastDue > 61 && $daysPastDue <= 90 ? $invoice->balance : 0,
                    'over_90' => $daysPastDue > 90 ? $invoice->balance : 0,
                ];
                $invoice->aging = $aging;
                $invoice->days_past_due = $daysPastDue > 0 ? $daysPastDue : 0;
                return $invoice;
            });

        $summary = [
            'current' => $invoices->sum(fn($i) => $i->aging['current']),
            '0-30' => $invoices->sum(fn($i) => $i->aging['0-30']),
            '31-60' => $invoices->sum(fn($i) => $i->aging['31-60']),
            '61-90' => $invoices->sum(fn($i) => $i->aging['61-90']),
            'over_90' => $invoices->sum(fn($i) => $i->aging['over_90']),
            'total' => $invoices->sum('balance'),
        ];

        return view('modules.accounting.accounts-receivable.aging-report', compact('invoices', 'summary', 'asOfDate'));
    }

    /**
     * Export Invoices PDF
     */
    private function exportInvoicesPdf($invoices)
    {
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.accounting.pdf.invoices', [
                'invoices' => $invoices,
                'company' => \App\Models\SystemSetting::getCompanyInfo(),
            ]);
            return $pdf->download('invoices_' . now()->format('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Invoice PDF Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Invoices Excel
     */
    private function exportInvoicesExcel($invoices, $request)
    {
        try {
            $filename = 'Invoices_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($invoices) {
                $file = fopen('php://output', 'w');
                
                // Header
                fputcsv($file, ['Invoice No', 'Customer', 'Date', 'Due Date', 'Total Amount', 'Paid Amount', 'Balance', 'Status']);
                
                // Data rows
                foreach ($invoices as $invoice) {
                    fputcsv($file, [
                        $invoice->invoice_no ?? '',
                        $invoice->customer->name ?? 'N/A',
                        $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : '',
                        $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '',
                        number_format($invoice->total_amount ?? 0, 2),
                        number_format($invoice->paid_amount ?? 0, 2),
                        number_format($invoice->balance ?? 0, 2),
                        $invoice->status ?? '',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Invoice Excel Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export Customers PDF
     */
    private function exportCustomersPdf($customers, $request)
    {
        try {
            $data = [
                'customers' => $customers,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
                'filters' => $request->only(['status', 'receivables', 'payment_terms', 'q']),
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.accounting.pdf.customers', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Customers_List_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Customers PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Customers Excel
     */
    private function exportCustomersExcel($customers, $request)
    {
        try {
            $filename = 'Customers_List_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($customers) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Code', 'Name', 'Contact Person', 'Email', 'Phone', 
                    'Credit Limit', 'Payment Terms', 
                    'Receivables', 'Status'
                ]);

                // Data rows
                foreach ($customers as $customer) {
                    fputcsv($file, [
                        $customer->customer_code ?? '',
                        $customer->name ?? '',
                        $customer->contact_person ?? '',
                        $customer->email ?? '',
                        $customer->phone ?? $customer->mobile ?? '',
                        number_format($customer->credit_limit ?? 0, 2),
                        ($customer->payment_terms_days ?? 30) . ' days',
                        number_format($customer->total_receivable ?? 0, 2),
                        $customer->is_active ? 'Active' : 'Inactive',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Customers Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    public function showCustomer($id)
    {
        $customer = Customer::with(['invoices' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        try {
            $customer->update([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'city' => $request->city,
                'tax_id' => $request->tax_id,
                'credit_limit' => $request->credit_limit ?? $customer->credit_limit,
                'payment_terms' => $request->payment_terms ?? $customer->payment_terms,
                'payment_terms_days' => $request->payment_terms_days ?? $customer->payment_terms_days,
                'is_active' => $request->has('is_active'),
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully! Changes have been saved.',
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Invoice
     */
    public function showInvoice($id)
    {
        try {
            $invoice = Invoice::with(['customer', 'items.account', 'payments'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Invoice
     */
    public function updateInvoice(Request $request, $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            if ($invoice->status === 'Paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit a paid invoice'
                ], 422);
            }

            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
                'items.*.account_id' => 'nullable|exists:chart_of_accounts,id',
            ]);

            DB::beginTransaction();

            // Update invoice
            $invoice->customer_id = $validated['customer_id'];
            $invoice->invoice_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'];
            
            // Calculate totals
            $subtotal = 0;
            $taxTotal = 0;
            
            foreach ($validated['items'] as $itemData) {
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $taxRate = $itemData['tax_rate'] ?? 0;
                
                $lineTotal = $quantity * $unitPrice;
                $lineTax = $lineTotal * ($taxRate / 100);
                
                $subtotal += $lineTotal;
                $taxTotal += $lineTax;
            }
            
            $invoice->subtotal = $subtotal;
            $invoice->tax_amount = $taxTotal;
            $invoice->total_amount = $subtotal + $taxTotal;
            $invoice->balance = $invoice->total_amount - $invoice->paid_amount;
            $invoice->updateStatus();
            $invoice->updated_by = Auth::id();
            $invoice->save();

            // Delete old items
            $invoice->items()->delete();

            // Create new items
            foreach ($validated['items'] as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'account_id' => $itemData['account_id'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'invoice' => $invoice->load('customer', 'items')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Credit Memo
     */
    public function showCreditMemo($id)
    {
        try {
            $creditMemo = CreditMemo::with(['customer', 'invoice.customer', 'creator'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'creditMemo' => [
                    'id' => $creditMemo->id,
                    'memo_no' => $creditMemo->memo_no,
                    'memo_date' => $creditMemo->memo_date ? $creditMemo->memo_date->format('Y-m-d') : '',
                    'type' => $creditMemo->type,
                    'amount' => $creditMemo->amount,
                    'reason' => $creditMemo->reason,
                    'status' => $creditMemo->status,
                    'customer' => $creditMemo->customer,
                    'invoice' => $creditMemo->invoice ? [
                        'id' => $creditMemo->invoice->id,
                        'invoice_no' => $creditMemo->invoice->invoice_no,
                        'invoice_date' => $creditMemo->invoice->invoice_date ? $creditMemo->invoice->invoice_date->format('Y-m-d') : '',
                        'total_amount' => $creditMemo->invoice->total_amount,
                        'balance' => $creditMemo->invoice->balance,
                        'status' => $creditMemo->invoice->status,
                    ] : null,
                    'created_by' => $creditMemo->creator ? $creditMemo->creator->name : 'N/A',
                    'created_at' => $creditMemo->created_at ? $creditMemo->created_at->format('Y-m-d H:i:s') : ''
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching credit memo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching credit memo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Credit Memo
     */
    public function updateCreditMemo(Request $request, $id)
    {
        try {
            $creditMemo = CreditMemo::findOrFail($id);
            
            if ($creditMemo->status !== 'Draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit a credit memo that is not in Draft status'
                ], 400);
            }

            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'memo_date' => 'required|date',
                'type' => 'required|in:Return,Discount,Adjustment,Write-off',
                'amount' => 'required|numeric|min:0.01',
                'invoice_id' => 'nullable|exists:invoices,id',
                'reason' => 'nullable|string',
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

            // If linked to invoice, revert previous amount
            if ($creditMemo->invoice_id) {
                $oldInvoice = Invoice::find($creditMemo->invoice_id);
                if ($oldInvoice) {
                    $oldInvoice->balance = min($oldInvoice->total_amount, $oldInvoice->balance + $creditMemo->amount);
                    $oldInvoice->updateStatus();
                    $oldInvoice->save();
                }
            }

            // Update credit memo
            $creditMemo->update([
                'invoice_id' => $validated['invoice_id'] ?? null,
                'customer_id' => $validated['customer_id'],
                'memo_date' => $validated['memo_date'],
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'reason' => $validated['reason'] ?? null,
            ]);

            // If linked to invoice, update invoice
            if (!empty($validated['invoice_id'])) {
                $invoice = Invoice::find($validated['invoice_id']);
                if ($invoice) {
                    $invoice->balance = max(0, $invoice->balance - $validated['amount']);
                    $invoice->updateStatus();
                    $invoice->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Credit memo updated successfully',
                'creditMemo' => $creditMemo->load('customer', 'invoice')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating credit memo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating credit memo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Credit Memo PDF
     */
    public function exportCreditMemoPdf($id)
    {
        try {
            $creditMemo = CreditMemo::with(['customer', 'invoice'])->findOrFail($id);
            
            $data = [
                'creditMemo' => $creditMemo,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.accounting.pdf.credit-memo', $data);
            $pdf->setPaper('A4', 'portrait');
            $filename = 'CreditMemo_' . $creditMemo->memo_no . '_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Credit Memo PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Approve Credit Memo
     */
    public function approveCreditMemo($id)
    {
        try {
            $creditMemo = CreditMemo::findOrFail($id);
            
            if ($creditMemo->status !== 'Pending for Approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Credit memo is not pending approval'
                ], 400);
            }

            DB::beginTransaction();
            
            $creditMemo->status = 'Approved';
            $creditMemo->save();

            // If approved, automatically post it
            if ($creditMemo->invoice_id) {
                $invoice = Invoice::find($creditMemo->invoice_id);
                if ($invoice) {
                    $invoice->balance = max(0, $invoice->balance - $creditMemo->amount);
                    $invoice->updateStatus();
                    $invoice->save();
                }
            }

            // Post to General Ledger - Double Entry Bookkeeping
            $customer = $creditMemo->customer;
            
            // Get Accounts Receivable account
            $arAccount = $customer->account_id ?? ChartOfAccount::where('code', 'AR')->first()?->id;
            
            if (!$arAccount) {
                $arAccount = ChartOfAccount::firstOrCreate(
                    ['code' => 'AR'],
                    [
                        'name' => 'Accounts Receivable',
                        'type' => 'Asset',
                        'category' => 'Current Asset',
                        'is_active' => true,
                    ]
                )->id;
            }
            
            // Get Revenue/Income account (to reduce revenue)
            $revenueAccount = ChartOfAccount::where('code', 'SALES')
                ->orWhere('code', 'REVENUE')
                ->orWhere('name', 'like', '%Sales Revenue%')
                ->where('type', 'Income')
                ->first()?->id;
            
            if (!$revenueAccount) {
                $revenueAccount = ChartOfAccount::firstOrCreate(
                    ['code' => 'SALES'],
                    [
                        'name' => 'Sales Revenue',
                        'type' => 'Income',
                        'category' => 'Operating Revenue',
                        'is_active' => true,
                    ]
                )->id;
            }
            
            // Ensure both accounts exist
            if (!$arAccount || !$revenueAccount) {
                throw new \Exception('Required Chart of Accounts not found. Please set up Accounts Receivable and Revenue accounts.');
            }
            
            // Debit: Revenue/Income Account (reduces revenue - opposite of invoice)
            GeneralLedger::create([
                'account_id' => $revenueAccount,
                'transaction_date' => $creditMemo->memo_date,
                'reference_type' => 'CreditMemo',
                'reference_id' => $creditMemo->id,
                'reference_no' => $creditMemo->memo_no,
                'type' => 'Debit',
                'amount' => $creditMemo->amount,
                'description' => "Credit memo {$creditMemo->memo_no} - {$creditMemo->type}: {$creditMemo->reason}",
                'source' => 'Credit Memo',
                'created_by' => Auth::id(),
            ]);
            
            // Credit: Accounts Receivable (reduces AR - customer owes less)
            GeneralLedger::create([
                'account_id' => $arAccount,
                'transaction_date' => $creditMemo->memo_date,
                'reference_type' => 'CreditMemo',
                'reference_id' => $creditMemo->id,
                'reference_no' => $creditMemo->memo_no,
                'type' => 'Credit',
                'amount' => $creditMemo->amount,
                'description' => "Credit memo {$creditMemo->memo_no} for {$customer->name}",
                'source' => 'Credit Memo',
                'created_by' => Auth::id(),
            ]);

            // Post to Posted status
            $creditMemo->status = 'Posted';
            $creditMemo->save();
            
            DB::commit();

            // Log activity
            ActivityLogService::logAction('credit_memo_approved', "Approved and posted credit memo {$creditMemo->memo_no} for TZS " . number_format($creditMemo->amount, 2), $creditMemo, [
                'memo_no' => $creditMemo->memo_no,
                'invoice_no' => $creditMemo->invoice->invoice_no ?? 'N/A',
                'customer_name' => $customer->name ?? 'N/A',
                'amount' => $creditMemo->amount,
                'type' => $creditMemo->type,
                'reason' => $creditMemo->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credit memo approved and posted successfully',
                'creditMemo' => $creditMemo->load('customer', 'invoice')
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving credit memo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving credit memo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject Credit Memo
     */
    public function rejectCreditMemo(Request $request, $id)
    {
        try {
            $creditMemo = CreditMemo::findOrFail($id);
            
            if ($creditMemo->status !== 'Pending for Approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Credit memo is not pending approval'
                ], 400);
            }

            $creditMemo->status = 'Rejected';
            $creditMemo->reason = ($creditMemo->reason ?? '') . "\n\nRejected: " . ($request->rejection_reason ?? 'No reason provided');
            $creditMemo->save();

            return response()->json([
                'success' => true,
                'message' => 'Credit memo rejected successfully',
                'creditMemo' => $creditMemo
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting credit memo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting credit memo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Invoice PDF
     */
    public function exportInvoicePdf($id)
    {
        try {
            $invoice = Invoice::with(['customer', 'items.account'])->findOrFail($id);
            
            $data = [
                'invoice' => $invoice,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.accounting.pdf.invoice', $data);
            $pdf->setPaper('A4', 'portrait');
            $filename = 'Invoice_' . $invoice->invoice_no . '_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Invoice PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Approve Invoice (HOD)
     */
    public function approveInvoice($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            if ($invoice->status !== 'Pending for Approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is not pending approval'
                ], 400);
            }

            $oldStatus = $invoice->status;
            $invoice->status = 'Approved';
            $invoice->updated_by = Auth::id();
            $invoice->save();

            // Log activity
            ActivityLogService::logApproved($invoice, "Invoice #{$invoice->invoice_number} approved", Auth::user()->name, [
                'old_status' => $oldStatus,
                'new_status' => 'Approved',
                'invoice_number' => $invoice->invoice_number,
                'invoice_amount' => $invoice->total_amount,
            ]);

            // Update status to Sent if needed
            $invoice->updateStatus();

            return response()->json([
                'success' => true,
                'message' => 'Invoice approved successfully',
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject Invoice (HOD)
     */
    public function rejectInvoice(Request $request, $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            if ($invoice->status !== 'Pending for Approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is not pending approval'
                ], 400);
            }

            $oldStatus = $invoice->status;
            $rejectionReason = $request->rejection_reason ?? 'No reason provided';
            $invoice->status = 'Rejected';
            $invoice->notes = ($invoice->notes ?? '') . "\n\nRejected: " . $rejectionReason;
            $invoice->updated_by = Auth::id();
            $invoice->save();

            // Log activity
            ActivityLogService::logRejected($invoice, "Invoice #{$invoice->invoice_number} rejected", Auth::user()->name, $rejectionReason, [
                'old_status' => $oldStatus,
                'new_status' => 'Rejected',
                'invoice_number' => $invoice->invoice_number,
                'invoice_amount' => $invoice->total_amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice rejected successfully',
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Invoice Payment
     */
    public function showInvoicePayment($id)
    {
        try {
            $payment = InvoicePayment::with(['invoice.customer', 'invoice.items', 'bankAccount', 'creator'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'payment_no' => $payment->payment_no,
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '',
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'reference_no' => $payment->reference_no,
                    'notes' => $payment->notes,
                    'invoice' => [
                        'id' => $payment->invoice->id,
                        'invoice_no' => $payment->invoice->invoice_no,
                        'invoice_date' => $payment->invoice->invoice_date ? $payment->invoice->invoice_date->format('Y-m-d') : '',
                        'due_date' => $payment->invoice->due_date ? $payment->invoice->due_date->format('Y-m-d') : '',
                        'total_amount' => $payment->invoice->total_amount,
                        'paid_amount' => $payment->invoice->paid_amount,
                        'balance' => $payment->invoice->balance,
                        'status' => $payment->invoice->status,
                        'customer' => $payment->invoice->customer,
                        'items' => $payment->invoice->items
                    ],
                    'bank_account' => $payment->bankAccount,
                    'created_by' => $payment->creator ? $payment->creator->name : 'N/A',
                    'created_at' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : ''
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Invoice Payment PDF
     */
    public function exportInvoicePaymentPdf($id)
    {
        try {
            $payment = InvoicePayment::with(['invoice.customer', 'invoice.items', 'bankAccount'])->findOrFail($id);
            
            $data = [
                'payment' => $payment,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.accounting.pdf.invoice-payment', $data);
            $pdf->setPaper('A4', 'portrait');
            $filename = 'Payment_' . $payment->payment_no . '_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Payment PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}

