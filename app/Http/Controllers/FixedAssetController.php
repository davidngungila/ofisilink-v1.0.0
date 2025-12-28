<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Models\FixedAssetCategory;
use App\Models\FixedAssetDepreciation;
use App\Models\FixedAssetDisposal;
use App\Models\FixedAssetMaintenance;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\GeneralLedger;
use App\Models\Department;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FixedAssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Asset Register - List all fixed assets
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403, 'Unauthorized access');
        }

        $query = FixedAsset::with(['category', 'department', 'assignedUser', 'vendor']);

        // Filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = FixedAssetCategory::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        // Statistics
        $stats = [
            'total_assets' => FixedAsset::count(),
            'total_value' => FixedAsset::sum('total_cost'),
            'total_depreciation' => FixedAsset::sum('accumulated_depreciation'),
            'net_book_value' => FixedAsset::sum('net_book_value'),
            'active_assets' => FixedAsset::where('status', 'Active')->count(),
        ];

        return view('modules.accounting.fixed-assets.index', compact('assets', 'categories', 'departments', 'stats'));
    }

    /**
     * Show single asset
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $asset = FixedAsset::with([
            'category', 'department', 'assignedUser', 'vendor',
            'depreciations' => function($q) {
                $q->orderBy('depreciation_date', 'desc')->limit(12);
            },
            'disposals', 'maintenanceRecords'
        ])->findOrFail($id);

        // Calculate current depreciation
        $currentDepreciation = $asset->calculateDepreciation();

        return view('modules.accounting.fixed-assets.show', compact('asset', 'currentDepreciation'));
    }

    /**
     * Create new asset form
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $categories = FixedAssetCategory::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        // Get chart of accounts for fixed assets
        $assetAccounts = ChartOfAccount::where('type', 'Asset')
            ->where('category', 'Fixed Asset')
            ->active()
            ->orderBy('code')
            ->get();
        
        $expenseAccounts = ChartOfAccount::where('type', 'Expense')
            ->where('category', 'Operating Expense')
            ->active()
            ->orderBy('code')
            ->get();

        return view('modules.accounting.fixed-assets.create', compact(
            'categories', 'departments', 'vendors', 'users', 'assetAccounts', 'expenseAccounts'
        ));
    }

    /**
     * Store new asset
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:fixed_asset_categories,id',
            'asset_code' => 'required|string|unique:fixed_assets,asset_code',
            'name' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'additional_costs' => 'nullable|numeric|min:0',
            'depreciation_method' => 'required|in:Straight Line,Declining Balance,Units of Production,Sum of Years Digits',
            'useful_life_years' => 'required|integer|min:1',
            'salvage_value' => 'nullable|numeric|min:0',
            'depreciation_start_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $totalCost = $validated['purchase_cost'] + ($request->additional_costs ?? 0);
            $usefulLifeYears = (int) $validated['useful_life_years'];
            $depreciationRate = $request->depreciation_rate ?? ($request->depreciation_method === 'Straight Line' 
                ? (100 / $usefulLifeYears) 
                : 0);
            
            $depreciationEndDate = Carbon::parse($validated['depreciation_start_date'])
                ->addYears($usefulLifeYears);

            $category = FixedAssetCategory::find($validated['category_id']);
            $categoryCode = $category ? $category->code : null;
            $barcodeNumber = FixedAsset::generateBarcodeNumber($categoryCode);

            $asset = FixedAsset::create([
                'category_id' => $validated['category_id'],
                'asset_code' => $validated['asset_code'],
                'barcode_number' => $barcodeNumber,
                'name' => $validated['name'],
                'description' => $request->description,
                'serial_number' => $request->serial_number,
                'manufacturer' => $request->manufacturer,
                'model' => $request->model,
                'location' => $request->location,
                'department_id' => $request->department_id,
                'assigned_to' => $request->assigned_to,
                'purchase_date' => $validated['purchase_date'],
                'purchase_cost' => $validated['purchase_cost'],
                'additional_costs' => $request->additional_costs ?? 0,
                'total_cost' => $totalCost,
                'vendor_id' => $request->vendor_id,
                'invoice_number' => $request->invoice_number,
                'purchase_order_number' => $request->purchase_order_number,
                'depreciation_method' => $validated['depreciation_method'],
                'depreciation_rate' => $depreciationRate,
                'useful_life_years' => $usefulLifeYears,
                'useful_life_units' => $request->useful_life_units,
                'salvage_value' => $request->salvage_value ?? 0,
                'depreciation_start_date' => $validated['depreciation_start_date'],
                'depreciation_end_date' => $depreciationEndDate,
                'accumulated_depreciation' => 0,
                'net_book_value' => $totalCost,
                'asset_account_id' => $request->asset_account_id,
                'depreciation_expense_account_id' => $request->depreciation_expense_account_id,
                'accumulated_depreciation_account_id' => $request->accumulated_depreciation_account_id,
                'warranty_period' => $request->warranty_period,
                'warranty_expiry' => $request->warranty_expiry,
                'notes' => $request->notes,
                'created_by' => $user->id,
            ]);

            // Create journal entry for asset purchase (optional - can be done separately)
            // Note: Journal entry creation for asset purchase should be done through the journal entries module

            DB::commit();

            return redirect()->route('modules.accounting.fixed-assets.show', $asset->id)
                ->with('success', 'Fixed asset created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating fixed asset: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error creating fixed asset: ' . $e->getMessage());
        }
    }

    /**
     * Edit asset form
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $asset = FixedAsset::findOrFail($id);
        $categories = FixedAssetCategory::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        $assetAccounts = ChartOfAccount::where('type', 'Asset')
            ->where('category', 'Fixed Asset')
            ->active()
            ->orderBy('code')
            ->get();
        
        $expenseAccounts = ChartOfAccount::where('type', 'Expense')
            ->where('category', 'Operating Expense')
            ->active()
            ->orderBy('code')
            ->get();

        return view('modules.accounting.fixed-assets.edit', compact(
            'asset', 'categories', 'departments', 'vendors', 'users', 'assetAccounts', 'expenseAccounts'
        ));
    }

    /**
     * Update asset
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $asset = FixedAsset::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:fixed_asset_categories,id',
            'asset_code' => 'required|string|unique:fixed_assets,asset_code,' . $id,
            'name' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'additional_costs' => 'nullable|numeric|min:0',
        ]);

        try {
            $totalCost = $validated['purchase_cost'] + ($request->additional_costs ?? 0);
            
            $asset->update([
                'category_id' => $validated['category_id'],
                'asset_code' => $validated['asset_code'],
                'name' => $validated['name'],
                'description' => $request->description,
                'serial_number' => $request->serial_number,
                'manufacturer' => $request->manufacturer,
                'model' => $request->model,
                'location' => $request->location,
                'department_id' => $request->department_id,
                'assigned_to' => $request->assigned_to,
                'purchase_date' => $validated['purchase_date'],
                'purchase_cost' => $validated['purchase_cost'],
                'additional_costs' => $request->additional_costs ?? 0,
                'total_cost' => $totalCost,
                'vendor_id' => $request->vendor_id,
                'invoice_number' => $request->invoice_number,
                'purchase_order_number' => $request->purchase_order_number,
                'warranty_period' => $request->warranty_period,
                'warranty_expiry' => $request->warranty_expiry,
                'notes' => $request->notes,
                'updated_by' => $user->id,
            ]);

            $asset->updateNetBookValue();

            return redirect()->route('modules.accounting.fixed-assets.show', $asset->id)
                ->with('success', 'Fixed asset updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating fixed asset: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error updating fixed asset: ' . $e->getMessage());
        }
    }

    /**
     * Delete asset
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $asset = FixedAsset::findOrFail($id);

        if ($asset->depreciations()->where('is_posted', true)->exists()) {
            return back()->with('error', 'Cannot delete asset with posted depreciation entries.');
        }

        $asset->delete();

        return redirect()->route('modules.accounting.fixed-assets.index')
            ->with('success', 'Fixed asset deleted successfully.');
    }

    /**
     * Depreciation Management
     */
    public function depreciation(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $query = FixedAssetDepreciation::with(['fixedAsset.category', 'creator']);

        if ($request->has('asset_id') && $request->asset_id) {
            $query->where('fixed_asset_id', $request->asset_id);
        }

        if ($request->has('period') && $request->period) {
            $query->where('period', $request->period);
        }

        if ($request->has('posted') && $request->posted !== '') {
            $query->where('is_posted', $request->posted);
        }

        $depreciations = $query->orderBy('depreciation_date', 'desc')->paginate(20);
        $assets = FixedAsset::where('status', 'Active')->orderBy('name')->get();

        return view('modules.accounting.fixed-assets.depreciation', compact('depreciations', 'assets'));
    }

    /**
     * Calculate and create depreciation entries
     */
    public function calculateDepreciation(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'period' => 'required|string', // e.g., "2025-01" for January 2025
            'period_type' => 'required|in:Monthly,Quarterly,Yearly',
            'asset_ids' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $period = $validated['period'];
            $periodType = $validated['period_type'];
            
            // Determine depreciation date based on period
            $depreciationDate = $this->getDepreciationDateFromPeriod($period, $periodType);

            // Get assets to depreciate
            $assetsQuery = FixedAsset::where('status', 'Active')
                ->where('depreciation_start_date', '<=', $depreciationDate);
            
            if (!empty($validated['asset_ids'])) {
                $assetsQuery->whereIn('id', $validated['asset_ids']);
            }

            $assets = $assetsQuery->get();
            $createdCount = 0;

            foreach ($assets as $asset) {
                // Check if depreciation already exists for this period
                $existing = FixedAssetDepreciation::where('fixed_asset_id', $asset->id)
                    ->where('period', $period)
                    ->where('period_type', $periodType)
                    ->first();

                if ($existing) {
                    continue; // Skip if already calculated
                }

                // Calculate depreciation
                $calculation = $asset->calculateDepreciation($depreciationDate);
                
                if ($calculation['depreciation_amount'] <= 0) {
                    continue; // Skip if no depreciation
                }

                // Adjust for period type
                $depreciationAmount = $calculation['depreciation_amount'];
                if ($periodType === 'Quarterly') {
                    $depreciationAmount = $depreciationAmount * 3;
                } elseif ($periodType === 'Yearly') {
                    $depreciationAmount = $depreciationAmount * 12;
                }

                $accumulatedBefore = $asset->accumulated_depreciation;
                $accumulatedAfter = min($accumulatedBefore + $depreciationAmount, $asset->total_cost - $asset->salvage_value);
                $netBookValueAfter = $asset->total_cost - $accumulatedAfter;

                // Create depreciation record
                $depreciation = FixedAssetDepreciation::create([
                    'fixed_asset_id' => $asset->id,
                    'depreciation_date' => $depreciationDate,
                    'period' => $period,
                    'period_type' => $periodType,
                    'depreciation_amount' => $depreciationAmount,
                    'accumulated_depreciation_before' => $accumulatedBefore,
                    'accumulated_depreciation_after' => $accumulatedAfter,
                    'net_book_value_before' => $asset->net_book_value,
                    'net_book_value_after' => $netBookValueAfter,
                    'calculation_details' => $calculation,
                    'created_by' => $user->id,
                ]);

                // Update asset
                $asset->accumulated_depreciation = $accumulatedAfter;
                $asset->net_book_value = $netBookValueAfter;
                $asset->save();

                $createdCount++;
            }

            DB::commit();

            return redirect()->route('modules.accounting.fixed-assets.depreciation')
                ->with('success', "Depreciation calculated for {$createdCount} asset(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error calculating depreciation: ' . $e->getMessage());
            return back()->with('error', 'Error calculating depreciation: ' . $e->getMessage());
        }
    }

    /**
     * Post depreciation to journal entries
     */
    public function postDepreciation($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $depreciation = FixedAssetDepreciation::with('fixedAsset')->findOrFail($id);

        if ($depreciation->is_posted) {
            return back()->with('error', 'Depreciation already posted.');
        }

        try {
            DB::beginTransaction();

            $asset = $depreciation->fixedAsset;

            if (!$asset->depreciation_expense_account_id || !$asset->accumulated_depreciation_account_id) {
                return back()->with('error', 'Asset missing required chart of accounts.');
            }

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_no' => JournalEntry::generateEntryNo(),
                'entry_date' => $depreciation->depreciation_date,
                'reference_no' => 'DEP-' . str_pad($depreciation->id, 6, '0', STR_PAD_LEFT),
                'description' => "Depreciation for {$asset->name} - Period {$depreciation->period}",
                'status' => 'Draft',
                'source' => 'Asset',
                'source_ref' => 'FA-' . $asset->id,
                'created_by' => $user->id,
            ]);

            // Debit: Depreciation Expense
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $asset->depreciation_expense_account_id,
                'type' => 'Debit',
                'amount' => $depreciation->depreciation_amount,
                'description' => "Depreciation expense - {$asset->name}",
            ]);

            // Credit: Accumulated Depreciation
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $asset->accumulated_depreciation_account_id,
                'type' => 'Credit',
                'amount' => $depreciation->depreciation_amount,
                'description' => "Accumulated depreciation - {$asset->name}",
            ]);

            // Post journal entry (this will create general ledger entries)
            if ($journalEntry->post()) {
                // Update depreciation record
                $depreciation->update([
                    'is_posted' => true,
                    'posted_date' => now(),
                    'journal_entry_id' => $journalEntry->id,
                    'posted_by' => $user->id,
                ]);
            } else {
                throw new \Exception('Failed to post journal entry. Entry may not be balanced.');
            }

            DB::commit();

            return back()->with('success', 'Depreciation posted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error posting depreciation: ' . $e->getMessage());
            return back()->with('error', 'Error posting depreciation: ' . $e->getMessage());
        }
    }

    /**
     * Asset Reports
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $reportType = $request->report_type ?? 'valuation';

        $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $assets = FixedAsset::with(['category', 'department'])->get();

        $data = [
            'total_cost' => $assets->sum('total_cost'),
            'total_depreciation' => $assets->sum('accumulated_depreciation'),
            'net_book_value' => $assets->sum('net_book_value'),
            'by_category' => $assets->groupBy('category_id')->map(function($group) {
                return [
                    'category' => $group->first()->category->name ?? 'N/A',
                    'count' => $group->count(),
                    'total_cost' => $group->sum('total_cost'),
                    'net_book_value' => $group->sum('net_book_value'),
                ];
            }),
            'by_status' => $assets->groupBy('status')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_cost' => $group->sum('total_cost'),
                    'net_book_value' => $group->sum('net_book_value'),
                ];
            }),
        ];

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportReportPdf($reportType, $data, $startDate, $endDate);
        }

        return view('modules.accounting.fixed-assets.reports', compact('data', 'reportType', 'startDate', 'endDate'));
    }

    /**
     * Depreciation Schedule Report
     */
    public function depreciationSchedule(Request $request, $assetId = null)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        if ($assetId) {
            $asset = FixedAsset::with(['category', 'department'])->findOrFail($assetId);
            $depreciations = FixedAssetDepreciation::where('fixed_asset_id', $assetId)
                ->orderBy('depreciation_date', 'desc')
                ->get();
            
            if ($request->has('export') && $request->export === 'pdf') {
                return $this->exportDepreciationSchedulePdf($asset, $depreciations);
            }

            return view('modules.accounting.fixed-assets.depreciation-schedule', compact('asset', 'depreciations'));
        }

        // All assets schedule
        $assets = FixedAsset::where('status', 'Active')->with('depreciations')->get();
        
        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportAllDepreciationSchedulePdf($assets);
        }

        return view('modules.accounting.fixed-assets.depreciation-schedule-all', compact('assets'));
    }

    // Helper Methods

    private function getDepreciationDateFromPeriod($period, $periodType)
    {
        if ($periodType === 'Monthly') {
            // Format: "2025-01"
            return Carbon::createFromFormat('Y-m', $period)->endOfMonth();
        } elseif ($periodType === 'Quarterly') {
            // Format: "2025-Q1"
            $parts = explode('-Q', $period);
            $year = $parts[0];
            $quarter = $parts[1];
            $month = ($quarter - 1) * 3 + 3; // End of quarter
            return Carbon::create($year, $month)->endOfMonth();
        } else { // Yearly
            // Format: "2025"
            return Carbon::create($period, 12, 31);
        }
    }


    private function exportReportPdf($reportType, $data, $startDate, $endDate)
    {
        $pdf = Pdf::loadView('modules.accounting.fixed-assets.reports-pdf', compact('data', 'reportType', 'startDate', 'endDate'));
        return $pdf->download('fixed-assets-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportDepreciationSchedulePdf($asset, $depreciations)
    {
        $pdf = Pdf::loadView('modules.accounting.fixed-assets.depreciation-schedule-pdf', compact('asset', 'depreciations'));
        return $pdf->download('depreciation-schedule-' . $asset->asset_code . '-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportAllDepreciationSchedulePdf($assets)
    {
        $pdf = Pdf::loadView('modules.accounting.fixed-assets.depreciation-schedule-all-pdf', compact('assets'));
        return $pdf->download('all-depreciation-schedule-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate barcode for asset
     */
    public function generateBarcode($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $asset = FixedAsset::findOrFail($id);
        $asset->generateBarcodeIfNotExists();

        return response()->json([
            'success' => true,
            'barcode_number' => $asset->barcode_number,
            'message' => 'Barcode generated successfully'
        ]);
    }

    /**
     * Print barcode label for single asset
     */
    public function printBarcode($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $asset = FixedAsset::with(['category', 'department'])->findOrFail($id);
        $asset->generateBarcodeIfNotExists();

        return view('modules.accounting.fixed-assets.barcode-label', compact('asset'));
    }

    /**
     * Print barcode labels for multiple assets
     */
    public function printBarcodes(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $assetIds = $request->input('asset_ids', []);
        
        if (empty($assetIds)) {
            // If no IDs provided, get all active assets
            $assets = FixedAsset::with(['category', 'department'])
                ->where('status', 'Active')
                ->orderBy('asset_code')
                ->get();
        } else {
            $assets = FixedAsset::with(['category', 'department'])
                ->whereIn('id', $assetIds)
                ->orderBy('asset_code')
                ->get();
        }

        // Generate barcodes for assets that don't have them
        foreach ($assets as $asset) {
            $asset->generateBarcodeIfNotExists();
        }

        $labelType = $request->input('label_type', 'standard'); // standard, compact, metal

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportBarcodesPdf($assets, $labelType);
        }

        return view('modules.accounting.fixed-assets.barcode-labels', compact('assets', 'labelType'));
    }

    /**
     * Export barcodes as PDF
     */
    private function exportBarcodesPdf($assets, $labelType = 'standard')
    {
        $pdf = Pdf::loadView('modules.accounting.fixed-assets.barcode-labels-pdf', compact('assets', 'labelType'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('asset-barcodes-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate barcode image data
     */
    public static function generateBarcodeImage($barcodeNumber)
    {
        try {
            // First try using Picqer library if available
            if (class_exists('\Picqer\Barcode\BarcodeGeneratorPNG', true)) {
                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                return base64_encode($generator->getBarcode($barcodeNumber, $generator::TYPE_CODE_128, 2, 50));
            }
            
            // Fallback to built-in GD library barcode generator
            if (function_exists('imagecreate')) {
                return \App\Helpers\BarcodeGenerator::generateSimpleBarcode($barcodeNumber, 2, 50);
            }
            
            Log::warning('Barcode generation not available. GD library or Picqer package required.');
            return null;
        } catch (\Throwable $e) {
            Log::error('Barcode generation error: ' . $e->getMessage());
            // Try fallback
            if (function_exists('imagecreate')) {
                try {
                    return \App\Helpers\BarcodeGenerator::generateSimpleBarcode($barcodeNumber, 2, 50);
                } catch (\Exception $ex) {
                    Log::error('Fallback barcode generation error: ' . $ex->getMessage());
                }
            }
            return null;
        }
    }

    /**
     * Bulk generate barcodes for assets without barcodes
     */
    public function bulkGenerateBarcodes(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $assetIds = $request->input('asset_ids', []);
        
        if (empty($assetIds)) {
            // If no IDs provided, get all assets without barcodes
            $assets = FixedAsset::where(function($query) {
                $query->whereNull('barcode_number')
                      ->orWhere('barcode_number', '');
            })->get();
        } else {
            // Generate barcodes for selected assets (regenerate if they already have one)
            $assets = FixedAsset::whereIn('id', $assetIds)->get();
        }

        $generated = 0;
        foreach ($assets as $asset) {
            // Generate barcode if it doesn't exist, or regenerate if requested
            if (!$asset->barcode_number) {
                $asset->generateBarcodeIfNotExists();
                $generated++;
            } else {
                // If barcode exists, regenerate it
                $categoryCode = $asset->category ? $asset->category->code : null;
                $asset->barcode_number = FixedAsset::generateBarcodeNumber($categoryCode);
                $asset->save();
                $generated++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Generated {$generated} barcode(s) successfully",
            'count' => $generated
        ]);
    }

    /**
     * Scan barcode and display asset details
     */
    public function scanBarcode(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $barcodeNumber = $request->input('barcode');
        
        if (!$barcodeNumber) {
            return view('modules.accounting.fixed-assets.scan-barcode');
        }

        $asset = FixedAsset::with(['category', 'department', 'assignedUser', 'vendor'])
            ->where('barcode_number', $barcodeNumber)
            ->first();

        if (!$asset) {
            return view('modules.accounting.fixed-assets.scan-barcode', [
                'error' => 'Asset not found with barcode: ' . $barcodeNumber,
                'barcode' => $barcodeNumber
            ]);
        }

        return view('modules.accounting.fixed-assets.scan-barcode', compact('asset', 'barcodeNumber'));
    }

    /**
     * Category Management
     */
    public function categories()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $categories = FixedAssetCategory::with(['assetAccount', 'depreciationExpenseAccount', 'accumulatedDepreciationAccount'])
            ->orderBy('name')
            ->get();
        
        $assetAccounts = ChartOfAccount::where('type', 'Asset')
            ->where('category', 'Fixed Asset')
            ->active()
            ->orderBy('code')
            ->get();
        
        $expenseAccounts = ChartOfAccount::where('type', 'Expense')
            ->where('category', 'Operating Expense')
            ->active()
            ->orderBy('code')
            ->get();

        return view('modules.accounting.fixed-assets.categories', compact('categories', 'assetAccounts', 'expenseAccounts'));
    }

    public function getCategory($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $category = FixedAssetCategory::with(['assetAccount', 'depreciationExpenseAccount', 'accumulatedDepreciationAccount'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    public function storeCategory(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:fixed_asset_categories,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'depreciation_method' => 'required|in:Straight Line,Declining Balance,Units of Production,Sum of Years Digits',
            'default_depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'default_useful_life_years' => 'required|integer|min:1|max:100',
            'asset_account_id' => 'nullable|exists:chart_of_accounts,id',
            'depreciation_expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        try {
            $category = FixedAssetCategory::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $request->description,
                'depreciation_method' => $validated['depreciation_method'],
                'default_depreciation_rate' => $request->default_depreciation_rate ?? 0,
                'default_useful_life_years' => $validated['default_useful_life_years'],
                'asset_account_id' => $request->asset_account_id,
                'depreciation_expense_account_id' => $request->depreciation_expense_account_id,
                'accumulated_depreciation_account_id' => $request->accumulated_depreciation_account_id,
                'is_active' => $request->has('is_active') ? true : false,
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category->load(['assetAccount', 'depreciationExpenseAccount', 'accumulatedDepreciationAccount'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCategory(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $category = FixedAssetCategory::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:fixed_asset_categories,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'depreciation_method' => 'required|in:Straight Line,Declining Balance,Units of Production,Sum of Years Digits',
            'default_depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'default_useful_life_years' => 'required|integer|min:1|max:100',
            'asset_account_id' => 'nullable|exists:chart_of_accounts,id',
            'depreciation_expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        try {
            $category->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $request->description,
                'depreciation_method' => $validated['depreciation_method'],
                'default_depreciation_rate' => $request->default_depreciation_rate ?? 0,
                'default_useful_life_years' => $validated['default_useful_life_years'],
                'asset_account_id' => $request->asset_account_id,
                'depreciation_expense_account_id' => $request->depreciation_expense_account_id,
                'accumulated_depreciation_account_id' => $request->accumulated_depreciation_account_id,
                'is_active' => $request->has('is_active') ? true : false,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category->load(['assetAccount', 'depreciationExpenseAccount', 'accumulatedDepreciationAccount'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyCategory($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $category = FixedAssetCategory::findOrFail($id);

        if ($category->assets()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing assets. Please reassign or delete assets first.'
            ], 400);
        }

        try {
            $category->delete();
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], 500);
        }
    }
}

