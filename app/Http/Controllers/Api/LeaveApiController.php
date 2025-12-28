<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveApiController extends Controller
{
    /**
     * Get all leave requests (filtered by role)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO']);
        
        $query = LeaveRequest::with(['employee:id,name,email', 'leaveType:id,name']);
        
        if (!$isManager) {
            $query->where('employee_id', $user->id);
        } else {
            // Managers see requests based on their role
            if ($user->hasRole('HOD')) {
                $query->whereHas('employee', function($q) use ($user) {
                    $q->where('primary_department_id', $user->primary_department_id);
                });
            }
        }
        
        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        
        $leaves = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $leaves->map(function ($leave) {
                return $this->formatLeave($leave);
            }),
            'pagination' => [
                'current_page' => $leaves->currentPage(),
                'total' => $leaves->total(),
                'per_page' => $leaves->perPage(),
                'last_page' => $leaves->lastPage(),
            ]
        ]);
    }

    /**
     * Get my leave requests
     */
    public function myLeaves(Request $request)
    {
        $user = Auth::user();
        
        $leaves = LeaveRequest::where('employee_id', $user->id)
            ->with(['leaveType:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($leave) {
                return $this->formatLeave($leave);
            });

        return response()->json([
            'success' => true,
            'data' => $leaves
        ]);
    }

    /**
     * Get pending leave requests (for managers)
     */
    public function pending(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = LeaveRequest::with(['employee:id,name,email', 'leaveType:id,name'])
            ->whereIn('status', ['pending', 'pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval']);
        
        if ($user->hasRole('HOD')) {
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('primary_department_id', $user->primary_department_id);
            });
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $leaves->map(function ($leave) {
                return $this->formatLeave($leave);
            })
        ]);
    }

    /**
     * Get single leave request
     */
    public function show($id)
    {
        $user = Auth::user();
        $leave = LeaveRequest::with(['employee', 'leaveType', 'reviewer', 'dependents'])
            ->findOrFail($id);

        // Check access
        if ($leave->employee_id != $user->id && 
            !$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatLeave($leave, true)
        ]);
    }

    /**
     * Create leave request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'dependents' => 'sometimes|array',
            'dependents.*.name' => 'required|string',
            'dependents.*.relationship' => 'required|string',
            'dependents.*.fare_amount' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $leave = LeaveRequest::create([
            'employee_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Add dependents if provided
        if ($request->has('dependents')) {
            foreach ($request->dependents as $dependent) {
                $leave->dependents()->create($dependent);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave request created successfully',
            'data' => $this->formatLeave($leave->load('leaveType'))
        ], 201);
    }

    /**
     * Update leave request
     */
    public function update(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $user = Auth::user();

        if ($leave->employee_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!in_array($leave->status, ['pending', 'pending_hr_review'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update leave request in current status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'reason' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $leave->update($request->only(['start_date', 'end_date', 'reason']));

        return response()->json([
            'success' => true,
            'message' => 'Leave request updated successfully',
            'data' => $this->formatLeave($leave->load('leaveType'))
        ]);
    }

    /**
     * Cancel leave request
     */
    public function cancel($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $user = Auth::user();

        if ($leave->employee_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!in_array($leave->status, ['pending', 'pending_hr_review', 'pending_hod_approval'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel leave request in current status'
            ], 422);
        }

        $leave->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Leave request cancelled successfully'
        ]);
    }

    /**
     * Approve leave request
     */
    public function approve(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $leave->update([
            'status' => 'approved',
            'reviewer_id' => $user->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully'
        ]);
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $leave = LeaveRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $leave->update([
            'status' => 'rejected',
            'reviewer_id' => $user->id,
            'reviewed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected'
        ]);
    }

    /**
     * Get leave balance
     */
    public function balance()
    {
        $user = Auth::user();
        
        $balances = LeaveBalance::where('user_id', $user->id)
            ->with('leaveType:id,name')
            ->get()
            ->map(function ($balance) {
                return [
                    'leave_type' => [
                        'id' => $balance->leaveType->id,
                        'name' => $balance->leaveType->name,
                    ],
                    'total_days' => $balance->total_days,
                    'used_days' => $balance->used_days,
                    'remaining_days' => $balance->remaining_days,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $balances
        ]);
    }

    /**
     * Get leave types
     */
    public function types()
    {
        $types = LeaveType::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description,
                    'max_days' => $type->max_days,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    private function formatLeave($leave, $detailed = false)
    {
        $data = [
            'id' => $leave->id,
            'employee' => [
                'id' => $leave->employee->id,
                'name' => $leave->employee->name,
                'email' => $leave->employee->email,
            ],
            'leave_type' => [
                'id' => $leave->leaveType->id,
                'name' => $leave->leaveType->name,
            ],
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'days' => Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1,
            'reason' => $leave->reason,
            'status' => $leave->status,
            'created_at' => $leave->created_at->toIso8601String(),
        ];

        if ($detailed) {
            $data['reviewer'] = $leave->reviewer ? [
                'id' => $leave->reviewer->id,
                'name' => $leave->reviewer->name,
            ] : null;
            $data['reviewed_at'] = $leave->reviewed_at ? $leave->reviewed_at->toIso8601String() : null;
            $data['dependents'] = $leave->dependents->map(function ($dep) {
                return [
                    'name' => $dep->name,
                    'relationship' => $dep->relationship,
                    'fare_amount' => $dep->fare_amount,
                ];
            });
        }

        return $data;
    }
}







