<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PettyCashVoucher;
use App\Models\ImprestRequest;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanceLedgerController extends Controller
{
	public function data(Request $request)
	{
		try {
			$validated = $request->validate([
				'date_from' => 'nullable|date',
				'date_to' => 'nullable|date',
				'account' => 'nullable|string',
				'source' => 'nullable|string|in:petty_cash,imprest,payroll',
				'q' => 'nullable|string',
				'page' => 'nullable|integer|min:1',
				'per_page' => 'nullable|integer|min:5|max:100'
			]);
			
			$dateFrom = $validated['date_from'] ?? null;
			$dateTo = $validated['date_to'] ?? null;
			$q = $validated['q'] ?? null;
			$account = $validated['account'] ?? null;
			$source = $validated['source'] ?? null;
			$perPage = $validated['per_page'] ?? 20;
			
			$entries = collect();
		
        $petty = PettyCashVoucher::query()
            ->with(['creator','lines'])
			->when($dateFrom, fn($q) => $q->whereDate('paid_at', '>=', $dateFrom))
			->when($dateTo, fn($q) => $q->whereDate('paid_at', '<=', $dateTo))
			->whereNotNull('paid_at')
			->get()
			->map(function($v){
				return [
					'date' => optional($v->paid_at)->format('Y-m-d'),
					'description' => 'Petty Cash Payment: '.($v->voucher_no ?? ('VC-'.str_pad($v->id,5,'0',STR_PAD_LEFT))),
					'account' => 'Petty Cash',
					'ref' => $v->id,
					'debit' => 0,
                    'credit' => (float)($v->amount ?? $v->lines->sum('total') ?? 0),
					'party' => optional($v->creator)->name,
					'source' => 'petty_cash'
				];
			});
		
		$imprest = collect();
		if (class_exists(\App\Models\ImprestRequest::class)) {
			$imprest = ImprestRequest::query()
				->when($dateFrom, fn($q) => $q->whereDate('paid_at','>=',$dateFrom))
				->when($dateTo, fn($q) => $q->whereDate('paid_at','<=',$dateTo))
				->whereNotNull('paid_at')
				->get()
				->map(function($r){
					return [
						'date' => optional($r->paid_at)->format('Y-m-d'),
						'description' => 'Imprest Paid: '.($r->purpose ?? '—'),
						'account' => 'Imprest',
						'ref' => $r->request_no ?? $r->id,
						'debit' => 0,
						'credit' => (float)($r->amount ?? 0),
						'party' => optional($r->accountant)->name,
						'source' => 'imprest'
					];
				});
		}
		
		$payroll = collect();
		if (class_exists(\App\Models\Payroll::class)) {
			$payroll = Payroll::query()
				->when($dateFrom, fn($q) => $q->whereDate('pay_date','>=',$dateFrom))
				->when($dateTo, fn($q) => $q->whereDate('pay_date','<=',$dateTo))
				->get()
				->map(function($p){
					$amount = (float)($p->total_amount ?? $p->net_salary ?? 0);
					return [
						'date' => optional($p->pay_date)->format('Y-m-d'),
						'description' => 'Payroll',
						'account' => 'Payroll',
						'ref' => $p->id,
						'debit' => 0,
						'credit' => $amount,
						'party' => optional($p->user)->name ?? '—',
						'source' => 'payroll'
					];
				});
		}
		
			$entries = $entries->merge($petty)->merge($imprest)->merge($payroll);
			
			if ($q) {
				$entries = $entries->filter(function($e) use ($q){
					return str_contains(strtolower($e['description']), strtolower($q))
						|| str_contains(strtolower($e['account']), strtolower($q))
						|| str_contains((string)$e['ref'], $q)
						|| str_contains(strtolower($e['party'] ?? ''), strtolower($q));
				});
			}
			if ($account) {
				$entries = $entries->where('account', $account);
			}
			
			if ($source) {
				$entries = $entries->where('source', $source);
			}
			
			$entries = $entries->sortByDesc('date')->values();
			
			$totalDebit = round($entries->sum('debit'), 2);
			$totalCredit = round($entries->sum('credit'), 2);
			$balance = round($totalDebit - $totalCredit, 2);
			
			$page = max(1, (int)($request->input('page', 1)));
			$chunks = $entries->forPage($page, $perPage)->values();
			
			return response()->json([
				'success' => true,
				'summary' => [
					'total_debit' => $totalDebit,
					'total_credit' => $totalCredit,
					'balance' => $balance,
					'count' => $entries->count()
				],
				'entries' => $chunks,
				'page' => $page,
				'per_page' => (int)$perPage
			]);
		} catch (\Exception $e) {
			\Log::error('Ledger data error: ' . $e->getMessage());
			return response()->json([
				'success' => false,
				'message' => 'Error loading ledger data: ' . $e->getMessage(),
				'summary' => [
					'total_debit' => 0,
					'total_credit' => 0,
					'balance' => 0,
					'count' => 0
				],
				'entries' => []
			], 500);
		}
	}

	public function exportPdf(Request $request)
	{
		try {
			// Extract filter parameters from GET request
			$dateFrom = $request->input('date_from');
			$dateTo = $request->input('date_to');
			$q = $request->input('q');
			$account = $request->input('account');
			$source = $request->input('source');
			
			$entries = collect();
		
			// Fetch Petty Cash entries
			$petty = PettyCashVoucher::query()
				->with(['creator','lines'])
				->when($dateFrom, fn($query) => $query->whereDate('paid_at', '>=', $dateFrom))
				->when($dateTo, fn($query) => $query->whereDate('paid_at', '<=', $dateTo))
				->whereNotNull('paid_at')
				->get()
				->map(function($v){
					return [
						'date' => optional($v->paid_at)->format('Y-m-d'),
						'description' => 'Petty Cash Payment: '.($v->voucher_no ?? ('VC-'.str_pad($v->id,5,'0',STR_PAD_LEFT))),
						'account' => 'Petty Cash',
						'ref' => $v->id,
						'debit' => 0,
						'credit' => (float)($v->amount ?? $v->lines->sum('total') ?? 0),
						'party' => optional($v->creator)->name,
						'source' => 'petty_cash'
					];
				});
			
			// Fetch Imprest entries
			$imprest = collect();
			if (class_exists(\App\Models\ImprestRequest::class)) {
				$imprest = ImprestRequest::query()
					->when($dateFrom, fn($query) => $query->whereDate('paid_at','>=',$dateFrom))
					->when($dateTo, fn($query) => $query->whereDate('paid_at','<=',$dateTo))
					->whereNotNull('paid_at')
					->get()
					->map(function($r){
						return [
							'date' => optional($r->paid_at)->format('Y-m-d'),
							'description' => 'Imprest Paid: '.($r->purpose ?? '—'),
							'account' => 'Imprest',
							'ref' => $r->request_no ?? $r->id,
							'debit' => 0,
							'credit' => (float)($r->amount ?? 0),
							'party' => optional($r->accountant)->name,
							'source' => 'imprest'
						];
					});
			}
			
			// Fetch Payroll entries
			$payroll = collect();
			if (class_exists(\App\Models\Payroll::class)) {
				$payroll = Payroll::query()
					->when($dateFrom, fn($query) => $query->whereDate('pay_date','>=',$dateFrom))
					->when($dateTo, fn($query) => $query->whereDate('pay_date','<=',$dateTo))
					->get()
					->map(function($p){
						$amount = (float)($p->total_amount ?? $p->net_salary ?? 0);
						return [
							'date' => optional($p->pay_date)->format('Y-m-d'),
							'description' => 'Payroll',
							'account' => 'Payroll',
							'ref' => $p->id,
							'debit' => 0,
							'credit' => $amount,
							'party' => optional($p->user)->name ?? '—',
							'source' => 'payroll'
						];
					});
			}
			
			// Merge all entries
			$entries = $entries->merge($petty)->merge($imprest)->merge($payroll);
			
			// Apply filters
			if ($q) {
				$entries = $entries->filter(function($e) use ($q){
					return str_contains(strtolower($e['description']), strtolower($q))
						|| str_contains(strtolower($e['account']), strtolower($q))
						|| str_contains((string)$e['ref'], $q)
						|| str_contains(strtolower($e['party'] ?? ''), strtolower($q));
				});
			}
			if ($account) {
				$entries = $entries->where('account', $account);
			}
			if ($source) {
				$entries = $entries->where('source', $source);
			}
			
			// Sort entries
			$entries = $entries->sortByDesc('date')->values();
			
			// Calculate summary
			$totalDebit = round($entries->sum('debit'), 2);
			$totalCredit = round($entries->sum('credit'), 2);
			$balance = round($totalDebit - $totalCredit, 2);
			
			$data = [
				'summary' => [
					'total_debit' => $totalDebit,
					'total_credit' => $totalCredit,
					'balance' => $balance,
					'count' => $entries->count()
				],
				'entries' => $entries->all(),
				'generated_at' => now()->format('Y-m-d H:i'),
				'filters' => [
					'date_from' => $dateFrom,
					'date_to' => $dateTo,
					'account' => $account,
					'source' => $source,
					'q' => $q,
				],
			];

			$pdf = Pdf::loadView('modules.finance.ledger-pdf', $data)->setPaper('A4', 'landscape');
			$filename = 'General_Ledger_' . now()->format('Ymd_His') . '.pdf';
			return $pdf->download($filename);
		} catch (\Exception $e) {
			\Log::error('PDF Export Exception: ' . $e->getMessage(), [
				'trace' => $e->getTraceAsString()
			]);
			
			// If we're in a web context, redirect back. Otherwise, return error response
			if ($request->expectsJson() || $request->ajax()) {
				return response()->json([
					'success' => false,
					'message' => 'Failed to generate PDF: ' . $e->getMessage()
				], 500);
			}
			
			return redirect()->route('modules.finance.ledger')
				->with('error', 'Failed to generate PDF: ' . $e->getMessage());
		}
	}
}

