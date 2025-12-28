<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PettyCashVoucher;
use App\Models\ImprestRequest;
use App\Models\ImprestAssignment;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FinanceApiController extends Controller
{
    // Petty Cash
    
    public function pettyCashStats(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO']);
        
        $query = PettyCashVoucher::query();
        
        if (!$isManager) {
            $query->where('user_id', $user->id);
        }
        
        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending_accountant')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'retired' => (clone $query)->where('status', 'retired')->count(),
            'pending_amount' => (clone $query)->where('status', 'pending_accountant')->sum('amount'),
            'approved_amount' => (clone $query)->where('status', 'approved')->sum('amount'),
            'paid_amount' => (clone $query)->where('status', 'paid')->sum('amount'),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    public function pettyCashIndex(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO']);
        
        $query = PettyCashVoucher::with(['user:id,name,email']);
        
        if (!$isManager) {
            $query->where('user_id', $user->id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $vouchers = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $vouchers->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'voucher_number' => $voucher->voucher_number,
                    'amount' => $voucher->amount,
                    'status' => $voucher->status,
                    'user' => $voucher->user ? $voucher->user->name : null,
                    'created_at' => $voucher->created_at->toIso8601String(),
                ];
            })
        ]);
    }

    public function pettyCashShow($id)
    {
        $voucher = PettyCashVoucher::with(['user', 'lines'])->findOrFail($id);
        $user = Auth::user();

        if ($voucher->user_id != $user->id && 
            !$user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $voucher
        ]);
    }

    public function pettyCashStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'purpose' => 'required|string',
            'items' => 'required|array',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $voucher = PettyCashVoucher::create([
            'user_id' => $user->id,
            'voucher_number' => 'PCV' . date('Ymd') . str_pad(PettyCashVoucher::count() + 1, 4, '0', STR_PAD_LEFT),
            'amount' => $request->amount,
            'purpose' => $request->purpose,
            'status' => 'pending_accountant',
        ]);

        foreach ($request->items as $item) {
            $voucher->lines()->create([
                'description' => $item['description'],
                'amount' => $item['amount'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Petty cash voucher created successfully',
            'data' => $voucher
        ], 201);
    }

    public function pettyCashUpdate(Request $request, $id)
    {
        $voucher = PettyCashVoucher::findOrFail($id);
        $user = Auth::user();

        if ($voucher->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!in_array($voucher->status, ['pending_accountant', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update voucher in current status'
            ], 422);
        }

        $voucher->update($request->only(['amount', 'purpose']));

        return response()->json([
            'success' => true,
            'message' => 'Voucher updated successfully'
        ]);
    }

    public function pettyCashApprove($id)
    {
        $voucher = PettyCashVoucher::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $voucher->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Voucher approved'
        ]);
    }

    public function pettyCashReject($id)
    {
        $voucher = PettyCashVoucher::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $voucher->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Voucher rejected'
        ]);
    }

    // Imprest

    public function imprestIndex(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO']);
        
        $query = ImprestRequest::with(['accountant:id,name,email']);
        
        if (!$isManager) {
            $query->where('accountant_id', $user->id);
        }
        
        $requests = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function imprestShow($id)
    {
        $request = ImprestRequest::with(['accountant', 'assignments'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $request
        ]);
    }

    public function imprestStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'purpose' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $imprest = ImprestRequest::create([
            'accountant_id' => $user->id,
            'request_number' => 'IMP' . date('Ymd') . str_pad(ImprestRequest::count() + 1, 4, '0', STR_PAD_LEFT),
            'amount' => $request->amount,
            'purpose' => $request->purpose,
            'status' => 'pending_hod',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Imprest request created successfully',
            'data' => $imprest
        ], 201);
    }

    public function imprestApprove($id)
    {
        $request = ImprestRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Imprest request approved'
        ]);
    }

    public function imprestReject($id)
    {
        $request = ImprestRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Imprest request rejected'
        ]);
    }

    public function imprestSubmitReceipt(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'receipt_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'amount_used' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $imprest = ImprestRequest::findOrFail($id);
        $assignment = ImprestAssignment::where('imprest_request_id', $imprest->id)->firstOrFail();

        $file = $request->file('receipt_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('imprest_receipts', $filename, 'public');

        $assignment->update([
            'receipt_file' => $path,
            'amount_used' => $request->amount_used,
            'receipt_submitted' => true,
            'receipt_submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Receipt submitted successfully'
        ]);
    }

    // Payroll

    public function payrollIndex(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'Accountant', 'HR Officer', 'CEO']);
        
        $query = Payroll::with(['user:id,name,email']);
        
        if (!$isManager) {
            $query->where('user_id', $user->id);
        }
        
        $payrolls = $query->orderBy('pay_period', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $payrolls
        ]);
    }

    public function myPayroll(Request $request)
    {
        $user = Auth::user();
        
        $payrolls = Payroll::where('user_id', $user->id)
            ->with('items')
            ->orderBy('pay_period', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payrolls
        ]);
    }

    public function payrollShow($id)
    {
        $payroll = Payroll::with(['user', 'items'])->findOrFail($id);
        $user = Auth::user();

        if ($payroll->user_id != $user->id && 
            !$user->hasAnyRole(['System Admin', 'Accountant', 'HR Officer', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $payroll
        ]);
    }
}






