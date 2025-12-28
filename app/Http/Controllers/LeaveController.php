<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveRecommendation;
use App\Models\LeaveDependent;
use App\Models\LeaveDocument;
use App\Models\User;
use App\Models\Department;
use App\Models\PettyCashVoucher;
use App\Models\PettyCashVoucherLine;
use App\Services\TanzaniaStatutoryCalculator;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request)
    {
        try {
        $user = Auth::user();
        
        // Determine access level
        $isHR = $user->hasRole('HR Officer');
        $isHOD = $user->hasRole('HOD');
        $isCEO = $user->hasRole('CEO');
        $isAdmin = $user->hasRole('System Admin');
        $isManager = $isHR || $isHOD || $isCEO || $isAdmin;
        
        // Get user's department for HOD filtering
        $userDepartmentId = null;
        if ($isHOD) {
            $userDepartmentId = $user->primary_department_id;
        }
        
            // Get leave requests based on user role with eager loading for performance
        $myRequests = $user->leaveRequests()
                ->with([
                    'leaveType:id,name,description',
                    'reviewer:id,name,email',
                    'documentProcessor:id,name,email',
                    'dependents:id,leave_request_id,name,relationship,fare_amount'
                ])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $awaitingMyAction = collect();
        $allOtherRequests = collect();
        
        if ($isManager) {
                // Get requests awaiting action with optimized eager loading
                $awaitingQuery = LeaveRequest::with([
                    'employee:id,name,email,primary_department_id',
                    'employee.primaryDepartment:id,name',
                    'leaveType:id,name,description',
                    'reviewer:id,name,email'
                ])
                ->where('employee_id', '!=', $user->id);
            
            if ($isHR || $isAdmin) {
                $awaitingQuery->whereIn('status', ['pending_hr_review', 'approved_pending_docs']);
            } elseif ($isHOD) {
                $awaitingQuery->where('status', 'pending_hod_approval')
                    ->whereHas('employee', function($query) use ($userDepartmentId) {
                        $query->where('primary_department_id', $userDepartmentId);
                    });
            } elseif ($isCEO) {
                $awaitingQuery->where('status', 'pending_ceo_approval');
            }
            
            $awaitingMyAction = $awaitingQuery->orderBy('created_at', 'desc')->get();
            
                // Get all other requests for managers with optimized eager loading
                $allOtherQuery = LeaveRequest::with([
                    'employee:id,name,email,primary_department_id',
                    'employee.primaryDepartment:id,name',
                    'leaveType:id,name,description',
                    'reviewer:id,name,email'
                ])
                ->where('employee_id', '!=', $user->id)
                ->whereNotIn('id', $awaitingMyAction->pluck('id'));
            
            if ($isHOD) {
                $allOtherQuery->whereHas('employee', function($query) use ($userDepartmentId) {
                    $query->where('primary_department_id', $userDepartmentId);
                });
            }
            
            $allOtherRequests = $allOtherQuery->orderBy('created_at', 'desc')->get();
        }
        
            $leaveTypes = LeaveType::where('is_active', true)
                ->select('id', 'name', 'description', 'max_days_per_year')
                ->orderBy('name')
                ->get();
                
            $departments = Department::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            
            // Calculate statistics with optimized queries
        $stats = [];
        if ($isManager) {
            $stats = [
                'total_pending_hr' => LeaveRequest::where('status', 'pending_hr_review')->count(),
                'total_pending_hod' => LeaveRequest::where('status', 'pending_hod_approval')->count(),
                'total_pending_ceo' => LeaveRequest::where('status', 'pending_ceo_approval')->count(),
                    'total_approved' => LeaveRequest::whereIn('status', ['on_leave', 'approved_pending_docs'])->count(),
                'total_on_leave' => LeaveRequest::where('status', 'on_leave')->count(),
                'total_completed' => LeaveRequest::where('status', 'completed')->count(),
                'total_this_month' => LeaveRequest::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count(),
                'total_pending_docs' => LeaveRequest::where('status', 'approved_pending_docs')->count(),
            ];
        } else {
            $stats = [
                'my_pending' => $myRequests->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval'])->count(),
                'my_approved' => $myRequests->whereIn('status', ['on_leave', 'approved_pending_docs'])->count(),
                'my_on_leave' => $myRequests->where('status', 'on_leave')->count(),
                'my_completed' => $myRequests->where('status', 'completed')->count(),
                'total_requests' => $myRequests->count(),
            ];
        }
        
        return view('modules.hr.leave', compact(
            'myRequests', 'awaitingMyAction', 'allOtherRequests', 
                'leaveTypes', 'departments', 'isManager', 'isHR', 'isHOD', 'isCEO', 'isAdmin', 'stats', 'userDepartmentId'
            ));
        } catch (\Exception $e) {
            \Log::error('Leave Management Index Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'An error occurred while loading the leave management page. Please try again.');
        }
    }
    
    public function getAnnualBalance(Request $request)
    {
        try {
            $user = Auth::user();
            $year = $request->input('year', date('Y'));
            
            // Find annual leave type first
            $annualType = LeaveType::where('name', 'like', '%annual%')->first();
            
            if (!$annualType) {
                // If no annual type exists, return default values
                return response()->json([
                    'success' => true,
                    'balance' => [
                        'total_days_allotted' => 28,
                        'days_taken' => 0,
                        'remaining_days' => 28,
                        'leave_type' => 'Annual Leave',
                    ]
                ]);
            }
            
            $balance = LeaveBalance::with('leaveType')
                ->where('employee_id', $user->id)
                ->where('financial_year', $year)
                ->where('leave_type_id', $annualType->id)
                ->first();
            
            if (!$balance) {
                // Create annual leave balance if it doesn't exist
                $balance = LeaveBalance::create([
                    'employee_id' => $user->id,
                    'leave_type_id' => $annualType->id,
                    'financial_year' => $year,
                    'total_days_allotted' => 28,
                    'days_taken' => 0,
                    'carry_forward_days' => 0,
                ]);
                $balance->load('leaveType');
            }
            
            return response()->json([
                'success' => true,
                'balance' => [
                    'total_days_allotted' => $balance->total_days_allotted ?? 28,
                    'days_taken' => $balance->days_taken ?? 0,
                    'remaining_days' => $balance->remaining_days ?? 28,
                    'leave_type' => $balance->leaveType ? $balance->leaveType->name : 'Annual Leave',
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('getAnnualBalance Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Could not load balance information: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getLeaveRecommendations(Request $request)
    {
        $user = Auth::user();
        $year = $request->input('year', date('Y'));
        $targetEmployeeId = $request->input('employee_id', $user->id);
        
        $recommendations = LeaveRecommendation::where('employee_id', $targetEmployeeId)
            ->where('financial_year', $year)
            ->where('status', 'approved')
            ->orderBy('recommended_start_date')
            ->get();
        
        $optimalPeriods = $this->getOptimalLeavePeriods($targetEmployeeId, $year);
        
        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
            'optimal_periods' => $optimalPeriods
        ]);
    }
    
    public function checkActiveLeave(Request $request)
    {
        $user = Auth::user();
        
        $activeRequests = LeaveRequest::where('employee_id', $user->id)
            ->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval', 'approved_pending_docs', 'on_leave'])
            ->get();
        
        if ($activeRequests->count() > 0) {
            $statusTexts = [
                'pending_hr_review' => 'HR Review',
                'pending_hod_approval' => 'HOD Approval',
                'pending_ceo_approval' => 'CEO Approval',
                'approved_pending_docs' => 'HR Document Processing',
                'on_leave' => 'Currently on Leave'
            ];
            
            $currentStatus = $statusTexts[$activeRequests->first()->status] ?? $activeRequests->first()->status;
            
            return response()->json([
                'success' => false,
                'message' => "You have an active leave request: {$currentStatus}. Please wait for it to be processed before submitting a new request."
            ]);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'leave_location' => 'required|string|max:255',
            'dependent_name' => 'array',
            'dependent_name.*' => 'string|max:255',
            'dependent_relationship' => 'array',
            'dependent_relationship.*' => 'string|max:100',
            'dependent_cert' => 'array',
            'dependent_cert.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        
        // Check for active leave requests
        $activeCheck = $this->checkActiveLeave($request);
        if (!$activeCheck->getData()->success) {
            return response()->json($activeCheck->getData());
        }
        
        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        // Validate against annual leave balance
        $annualBalance = LeaveBalance::where('employee_id', $user->id)
            ->where('financial_year', $startDate->year)
            ->whereHas('leaveType', function($query) {
                $query->where('name', 'like', '%annual%');
            })
            ->first();
        
        if ($annualBalance && $totalDays > $annualBalance->remaining_days) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient annual leave balance. You have {$annualBalance->remaining_days} days remaining."
            ]);
        }
        
        // Check for department overlap (for annual leave)
        $leaveType = LeaveType::find($request->leave_type_id);
        if ($leaveType && stripos($leaveType->name, 'annual') !== false) {
            $departmentId = $user->primary_department_id;
            if ($departmentId) {
                $staffCount = User::where('primary_department_id', $departmentId)
                    ->where('is_active', true)
                    ->count();
                
                $maxConcurrentLeaves = max(1, floor($staffCount * 0.3));
                
                $concurrentCount = LeaveRequest::whereHas('employee', function($query) use ($departmentId) {
                        $query->where('primary_department_id', $departmentId);
                    })
                    ->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval', 'approved_pending_docs', 'on_leave'])
                    ->where(function($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function($q) use ($startDate, $endDate) {
                                $q->where('start_date', '<=', $startDate)
                                  ->where('end_date', '>=', $endDate);
                            });
                    })
                    ->count();
                
                if ($concurrentCount >= $maxConcurrentLeaves) {
                    $recommendations = LeaveRecommendation::where('employee_id', $user->id)
                        ->where('financial_year', $startDate->year)
                        ->where('status', 'approved')
                        ->get();
                    
                    if ($recommendations->count() > 0) {
                        $recommendedText = $recommendations->map(function($rec) {
                            return $rec->recommended_start_date->format('M j') . ' - ' . $rec->recommended_end_date->format('M j');
                        })->join(', ');
                        
                        return response()->json([
                            'success' => false,
                            'message' => "Too many staff from your department are already on leave during this period. Your recommended periods: {$recommendedText}"
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "Too many staff from your department are already on leave during this period. Please choose different dates."
                        ]);
                    }
                }
            }
        }
        
        DB::beginTransaction();
        try {
            // Create leave request
            $leaveRequest = LeaveRequest::create([
                'employee_id' => $user->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'leave_location' => $request->leave_location,
                'status' => 'pending_hr_review',
            ]);
            
            // Handle dependents with improved error handling
            // Support both old format (dependent_name array) and new format (dependents array)
            $dependentsData = [];
            if ($request->has('dependents') && is_array($request->dependents)) {
                // New format: dependents[0][name], dependents[0][relationship], etc.
                $dependentsData = $request->dependents;
            } elseif ($request->has('dependent_name') && is_array($request->dependent_name)) {
                // Old format: dependent_name[0], dependent_relationship[0], etc.
                foreach ($request->dependent_name as $key => $name) {
                    if (!empty(trim($name))) {
                        $dependentsData[] = [
                            'name' => trim($name),
                            'relationship' => $request->dependent_relationship[$key] ?? '',
                            'fare_amount' => 0, // Only HR can assign fare amounts during review
                        ];
                    }
                }
            }
            
            foreach ($dependentsData as $key => $dependent) {
                if (empty(trim($dependent['name'] ?? ''))) continue;
                    
                    $dependentData = [
                        'leave_request_id' => $leaveRequest->id,
                    'name' => trim($dependent['name']),
                    'relationship' => $dependent['relationship'] ?? '',
                    'fare_amount' => 0, // Only HR can assign fare amounts during review
                    ];
                    
                    // Handle file upload with better validation
                    $fileKey = "dependent_cert.{$key}";
                    if ($request->hasFile($fileKey)) {
                        $file = $request->file($fileKey);
                        if ($file && $file->isValid()) {
                            try {
                                // Validate file type and size
                                $allowedMimes = ['pdf', 'jpg', 'jpeg', 'png'];
                                $extension = strtolower($file->getClientOriginalExtension());
                                
                                if (!in_array($extension, $allowedMimes)) {
                                    throw new \Exception("Invalid file type. Allowed types: PDF, JPG, JPEG, PNG");
                                }
                                
                                if ($file->getSize() > 2048 * 1024) { // 2MB in bytes
                                    throw new \Exception("File size exceeds 2MB limit");
                                }
                                
                                $path = $file->store('dependent_certs', 'public');
                                $dependentData['certificate_path'] = $path;
                            } catch (\Exception $e) {
                                \Log::warning("Failed to store dependent certificate for key {$key}: " . $e->getMessage());
                                // Continue without certificate rather than failing entire request
                            }
                        }
                    }
                    
                    try {
                    LeaveDependent::create($dependentData);
                    } catch (\Exception $e) {
                        \Log::error("Failed to create dependent for leave request {$leaveRequest->id}: " . $e->getMessage());
                        throw $e; // Re-throw to trigger rollback
                }
            }
            
            DB::commit();
            
            // Log activity
            ActivityLogService::logCreated($leaveRequest, "Created leave request for {$totalDays} days ({$startDate->format('M d')} - {$endDate->format('M d')})", [
                'leave_type_id' => $leaveRequest->leave_type_id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $totalDays,
                'status' => $leaveRequest->status,
            ]);
            
            // Send notifications with improved error handling
            try {
                $leaveType = LeaveType::find($leaveRequest->leave_type_id);
                $leaveTypeName = $leaveType ? $leaveType->name : 'Leave';
                
                // Notify staff
                try {
                $this->notificationService->notify(
                    $user->id,
                    "Your {$leaveTypeName} request for {$totalDays} days ({$startDate->format('M d')} - {$endDate->format('M d')}) has been submitted and is pending HR review.",
                    route('modules.hr.leave'),
                    'Leave Request Submitted'
                );
                } catch (\Exception $e) {
                    \Log::warning('Failed to notify employee about leave submission: ' . $e->getMessage());
                }

                // Notify HR
                try {
                $this->notificationService->notifyHR(
                    "New {$leaveTypeName} request from {$user->name} for {$totalDays} days ({$startDate->format('M d')} - {$endDate->format('M d')}) is pending your review.",
                    route('modules.hr.leave'),
                    'New Leave Request Pending Review',
                    ['staff_name' => $user->name, 'leave_type' => $leaveTypeName, 'days' => $totalDays]
                );
                } catch (\Exception $e) {
                    \Log::warning('Failed to notify HR about new leave request: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                \Log::error('Notification error in leave store: ' . $e->getMessage());
                // Don't fail the entire request if notifications fail
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Leave request submitted successfully for HR review!',
                'request_id' => $leaveRequest->id
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Leave request submission error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['dependent_cert'])
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit leave request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('employee_id', $user->id)
            ->where('status', 'rejected_for_edit')
            ->first();
        
        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found or cannot be edited.'
            ]);
        }
        
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'leave_location' => 'required|string|max:255',
            'dependent_name' => 'array',
            'dependent_name.*' => 'string|max:255',
            'dependent_relationship' => 'array',
            'dependent_relationship.*' => 'string|max:100',
            'dependent_cert' => 'array',
            'dependent_cert.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        
        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        DB::beginTransaction();
        try {
            // Update leave request
            $leaveRequest->update([
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'leave_location' => $request->leave_location,
                'status' => 'pending_hr_review',
            ]);
            
            // Delete existing dependents
            $leaveRequest->dependents()->delete();
            
            // Handle dependents
            if ($request->has('dependent_name') && is_array($request->dependent_name)) {
                foreach ($request->dependent_name as $key => $name) {
                    if (empty(trim($name))) continue;
                    
                    $dependentData = [
                        'leave_request_id' => $leaveRequest->id,
                        'name' => trim($name),
                        'relationship' => $request->dependent_relationship[$key] ?? '',
                        'fare_amount' => 0,
                    ];
                    
                    // Handle file upload - check both array and dot notation
                    if ($request->hasFile("dependent_cert.{$key}")) {
                        $file = $request->file("dependent_cert.{$key}");
                        if ($file && $file->isValid()) {
                            $path = $file->store('dependent_certs', 'public');
                            $dependentData['certificate_path'] = $path;
                        }
                    } elseif (isset($request->dependent_cert[$key]) && is_array($request->dependent_cert)) {
                        $file = $request->file("dependent_cert")[$key] ?? null;
                        if ($file && $file->isValid()) {
                            $path = $file->store('dependent_certs', 'public');
                            $dependentData['certificate_path'] = $path;
                        }
                    }
                    
                    LeaveDependent::create($dependentData);
                }
            }
            
            DB::commit();
            
            // Log activity
            $oldValues = array_intersect_key($leaveRequest->getOriginal(), $leaveRequest->getChanges());
            ActivityLogService::logUpdated($leaveRequest, $oldValues, $leaveRequest->getChanges(), "Updated leave request", [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
            ]);
            
            // Send notifications
            try {
                $leaveType = LeaveType::find($leaveRequest->leave_type_id);
                $leaveTypeName = $leaveType ? $leaveType->name : 'Leave';
                $startDate = Carbon::parse($leaveRequest->start_date);
                $endDate = Carbon::parse($leaveRequest->end_date);
                
                // Notify staff
                $this->notificationService->notify(
                    $user->id,
                    "Your updated {$leaveTypeName} request for {$leaveRequest->total_days} days ({$startDate->format('M d')} - {$endDate->format('M d')}) has been resubmitted and is pending HR review.",
                    route('modules.hr.leave'),
                    'Leave Request Resubmitted'
                );

                // Notify HR
                $this->notificationService->notifyHR(
                    "Updated {$leaveTypeName} request from {$user->name} for {$leaveRequest->total_days} days ({$startDate->format('M d')} - {$endDate->format('M d')}) is pending your review.",
                    route('modules.hr.leave'),
                    'Updated Leave Request Pending Review'
                );
            } catch (\Exception $e) {
                \Log::error('Notification error in leave update: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Leave request updated and resubmitted successfully!',
                'request_id' => $leaveRequest->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave request: ' . $e->getMessage()
            ]);
        }
    }
    
    public function show($id)
    {
        try {
        $user = Auth::user();
        
            // Eager load all necessary relationships with optimized selects
            $leaveRequest = LeaveRequest::with([
                'employee:id,name,email,primary_department_id',
                'employee.primaryDepartment:id,name',
                'leaveType:id,name,description,max_days_per_year',
                'dependents:id,leave_request_id,name,relationship,fare_amount,certificate_path',
                'reviewer:id,name,email',
                'documentProcessor:id,name,email'
            ])->findOrFail($id);
        
        // Check access
        if ($leaveRequest->employee_id !== $user->id && !$user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'System Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this leave request.'
                ], 403);
        }
        
        // For HOD, check if employee is in their department
        if ($user->hasRole('HOD') && !$user->hasRole('System Admin')) {
                if ($leaveRequest->employee && $leaveRequest->employee->primary_department_id !== $user->primary_department_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only view leave requests from your department.'
                    ], 403);
            }
        }
        
        return response()->json([
            'success' => true,
            'details' => $leaveRequest,
            'dependents' => $leaveRequest->dependents,
        ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Leave Request Show Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the leave request details.'
            ], 500);
        }
    }
    
    public function getRequestForEdit($id)
    {
        $user = Auth::user();
        
        $leaveRequest = LeaveRequest::with('dependents')
            ->where('id', $id)
            ->where('employee_id', $user->id)
            ->where('status', 'rejected_for_edit')
            ->first();
        
        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found or cannot be edited.'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'details' => $leaveRequest,
            'dependents' => $leaveRequest->dependents,
        ]);
    }
    
    public function hrReview(Request $request, $id)
    {
        try {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                    'message' => 'Authorization Failed: Only HR Officers and System Admins can review leave requests.'
                ], 403);
        }
        
            $leaveRequest = LeaveRequest::with(['employee', 'leaveType', 'dependents'])->findOrFail($id);
        
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any level, others must wait for pending_hr_review
        if (!$isSystemAdmin && $leaveRequest->status !== 'pending_hr_review') {
            return response()->json([
                'success' => false,
                    'message' => 'This request is not pending HR review. Current status: ' . $leaveRequest->status . '.'
                ], 422);
        }
        
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'required|string|max:1000',
            'fare_amount' => 'array',
            'fare_amount.*' => 'numeric|min:0',
            ], [
                'decision.required' => 'Please select a decision (approve or reject).',
                'decision.in' => 'Invalid decision. Must be either approve or reject.',
                'comments.required' => 'Comments are required for the review.',
                'comments.max' => 'Comments cannot exceed 1000 characters.',
                'fare_amount.array' => 'Fare amounts must be provided as an array.',
                'fare_amount.*.numeric' => 'Each fare amount must be a valid number.',
                'fare_amount.*.min' => 'Fare amounts cannot be negative.',
        ]);
        
        DB::beginTransaction();
        try {
            $newStatus = $request->decision === 'approve' ? 'pending_hod_approval' : 'rejected_for_edit';
            
            // Process fare amounts for dependents
            $totalFare = 0;
                if ($request->has('fare_amount') && is_array($request->fare_amount)) {
                foreach ($request->fare_amount as $depId => $fare) {
                    $dependent = LeaveDependent::find($depId);
                    if ($dependent && $dependent->leave_request_id == $id) {
                            $dependent->update(['fare_amount' => max(0, floatval($fare))]);
                            $totalFare += $dependent->fare_amount;
                    }
                }
            }
            
            $leaveRequest->update([
                'status' => $newStatus,
                    'hr_officer_comments' => trim($request->comments),
                'total_fare_approved' => $totalFare,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
            
            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('leave_hr_reviewed', ucfirst($request->decision) . " leave request by HR", $leaveRequest, [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'decision' => $request->decision,
                'comments' => $request->comments,
                'total_fare_approved' => $totalFare,
                'reviewed_by' => $user->name,
            ]);
            
                // Send notifications with improved error handling
            try {
                    $employee = $leaveRequest->employee;
                    $leaveType = $leaveRequest->leaveType;
                $leaveTypeName = $leaveType ? $leaveType->name : 'Leave';
                
                if ($request->decision === 'approve') {
                    // Notify HOD
                    if ($employee && $employee->primary_department_id) {
                            try {
                        $this->notificationService->notifyHOD(
                            $employee->primary_department_id,
                            "Leave request from {$employee->name} ({$leaveTypeName}, {$leaveRequest->total_days} days) has been reviewed by HR and is pending your approval.",
                            route('modules.hr.leave'),
                            'Leave Request Pending HOD Approval'
                        );
                            } catch (\Exception $e) {
                                \Log::warning('Failed to notify HOD in hrReview: ' . $e->getMessage());
                            }
                    }
                    
                    // Notify staff
                        try {
                    $this->notificationService->notify(
                        $employee->id,
                        "Your {$leaveTypeName} request has been reviewed by HR and forwarded to HOD for approval.",
                        route('modules.hr.leave'),
                        'Leave Request Status Update'
                    );
                        } catch (\Exception $e) {
                            \Log::warning('Failed to notify employee in hrReview: ' . $e->getMessage());
                        }
                } else {
                    // Notify staff of rejection
                        try {
                    $this->notificationService->notify(
                        $employee->id,
                        "Your {$leaveTypeName} request has been rejected by HR. Please review comments and resubmit.",
                        route('modules.hr.leave'),
                        'Leave Request Rejected'
                    );
                        } catch (\Exception $e) {
                            \Log::warning('Failed to notify employee of rejection in hrReview: ' . $e->getMessage());
                        }
                }
            } catch (\Exception $e) {
                \Log::error('Notification error in hrReview: ' . $e->getMessage());
                    // Don't fail the review if notifications fail
            }
            
            $message = $request->decision === 'approve' 
                    ? 'Review submitted successfully. Request forwarded to HOD for approval.' 
                    : 'Request rejected successfully. Employee has been notified to review comments and resubmit.';
            
            return response()->json([
                'success' => true,
                    'message' => $message,
                    'status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('HR Review Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process HR review. Please try again or contact support if the issue persists.'
            ], 500);
        }
    }
    
    public function hodReview(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: HOD access required.'
            ]);
        }
        
        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any level, others must wait for pending_hod_approval
        if (!$isSystemAdmin && $leaveRequest->status !== 'pending_hod_approval') {
            return response()->json([
                'success' => false,
                'message' => 'This request is not pending HOD approval.'
            ]);
        }
        
        // Check if HOD can review this request (same department) - System Admin bypasses this
        if ($user->hasRole('HOD') && !$isSystemAdmin) {
            if ($leaveRequest->employee->primary_department_id !== $user->primary_department_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only review requests from your department.'
                ]);
            }
        }
        
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'required|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            $newStatus = $request->decision === 'approve' ? 'pending_ceo_approval' : 'rejected_for_edit';
            
            $commentText = "HOD ({$user->name}): " . $request->comments;
            $existingComments = $leaveRequest->comments ? $leaveRequest->comments . "\n" : '';
            
            $leaveRequest->update([
                'status' => $newStatus,
                'comments' => $existingComments . $commentText,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
            
            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('leave_hod_reviewed', ucfirst($request->decision) . " leave request by HOD", $leaveRequest, [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'decision' => $request->decision,
                'comments' => $request->comments,
                'reviewed_by' => $user->name,
            ]);
            
            // Send notifications
            try {
                $employee = $leaveRequest->employee;
                $leaveType = LeaveType::find($leaveRequest->leave_type_id);
                $leaveTypeName = $leaveType ? $leaveType->name : 'Leave';
                
                if ($request->decision === 'approve') {
                    // Notify CEO
                    $this->notificationService->notifyCEO(
                        "Leave request from {$employee->name} ({$leaveTypeName}, {$leaveRequest->total_days} days) has been approved by HOD and is pending your final approval.",
                        route('modules.hr.leave'),
                        'Leave Request Pending CEO Approval'
                    );
                    
                    // Notify staff
                    $this->notificationService->notify(
                        $employee->id,
                        "Your {$leaveTypeName} request has been approved by HOD and forwarded to CEO for final approval.",
                        route('modules.hr.leave'),
                        'Leave Request Status Update'
                    );
                } else {
                    // Notify staff of rejection
                    $this->notificationService->notify(
                        $employee->id,
                        "Your {$leaveTypeName} request has been rejected by HOD. Please review comments and resubmit.",
                        route('modules.hr.leave'),
                        'Leave Request Rejected'
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Notification error in hodReview: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'HOD decision recorded successfully.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process HOD review: ' . $e->getMessage()
            ]);
        }
    }
    
    public function ceoReview(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['CEO', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: CEO access required.'
            ]);
        }
        
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any level, others must wait for pending_ceo_approval
        if (!$isSystemAdmin && $leaveRequest->status !== 'pending_ceo_approval') {
            return response()->json([
                'success' => false,
                'message' => 'This request is not pending CEO approval.'
            ]);
        }
        
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'required|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            $newStatus = $request->decision === 'approve' ? 'approved_pending_docs' : 'rejected';
            
            $commentText = "CEO ({$user->name}): " . $request->comments;
            $existingComments = $leaveRequest->comments ? $leaveRequest->comments . "\n" : '';
            
            $leaveRequest->update([
                'status' => $newStatus,
                'comments' => $existingComments . $commentText,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
            
            // Update leave balance if approved
            if ($newStatus === 'approved_pending_docs') {
                $year = $leaveRequest->start_date->year;
                $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                    ->where('financial_year', $year)
                    ->whereHas('leaveType', function($query) {
                        $query->where('name', 'like', '%annual%');
                    })
                    ->first();
                
                if ($balance) {
                    $balance->increment('days_taken', $leaveRequest->total_days);
                }
            }
            
            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('leave_ceo_reviewed', ucfirst($request->decision) . " leave request by CEO", $leaveRequest, [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'decision' => $request->decision,
                'comments' => $request->comments,
                'reviewed_by' => $user->name,
            ]);
            
            // Send notifications
            try {
                $employee = User::find($leaveRequest->employee_id);
                $leaveType = LeaveType::find($leaveRequest->leave_type_id);
                $leaveTypeName = $leaveType ? $leaveType->name : 'Leave';
                
                if ($request->decision === 'approve') {
                    // Notify HR for document processing
                    $this->notificationService->notifyHR(
                        "Leave request from {$employee->name} ({$leaveTypeName}, {$leaveRequest->total_days} days) has been approved by CEO. Please process documents.",
                        route('modules.hr.leave'),
                        'Leave Request Approved - Process Documents'
                    );
                    
                    // Notify staff
                    $this->notificationService->notify(
                        $employee->id,
                        "Your {$leaveTypeName} request has been approved by CEO! Please wait for HR to process your documents.",
                        route('modules.hr.leave'),
                        'Leave Request Approved'
                    );
                } else {
                    // Notify staff of rejection
                    $this->notificationService->notify(
                        $employee->id,
                        "Your {$leaveTypeName} request has been rejected by CEO.",
                        route('modules.hr.leave'),
                        'Leave Request Rejected'
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Notification error in ceoReview: ' . $e->getMessage());
            }
            
            $message = $request->decision === 'approve' 
                ? 'Final approval recorded. The request is now pending HR document processing.'
                : 'CEO rejection recorded successfully.';
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process CEO review: ' . $e->getMessage()
            ]);
        }
    }
    
    public function processDocuments(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: HR Officer access required.'
            ]);
        }
        
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        if ($leaveRequest->status !== 'approved_pending_docs') {
            return response()->json([
                'success' => false,
                'message' => 'This request is not pending HR document processing.'
            ]);
        }
        
        $request->validate([
            'approval_letter_number' => 'nullable|string|max:100',
            'approval_date' => 'required|date',
            'fare_approved_amount' => 'required|numeric|min:0',
            'payment_voucher_number' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'additional_notes' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            // Auto-generate approval letter number if not provided
            $approvalLetterNumber = $request->approval_letter_number;
            if (empty($approvalLetterNumber)) {
                $approvalLetterNumber = $this->generateApprovalLetterNumber($request->approval_date);
            }
            
            // Auto-generate payment voucher number if not provided
            $paymentVoucherNumber = $request->payment_voucher_number;
            if (empty($paymentVoucherNumber)) {
                $paymentVoucherNumber = $this->generatePaymentVoucherNumber($request->payment_date);
            }
            
            // Generate document numbers in date-001 format
            $dateStr = date('Ymd');
            $lastLeaveCert = LeaveRequest::whereNotNull('leave_certificate_number')
                ->whereDate('created_at', today())
                ->where('leave_certificate_number', 'like', "LC-{$dateStr}-%")
                ->orderBy('leave_certificate_number', 'desc')
                ->value('leave_certificate_number');
            
            $leaveCertSeq = 1;
            if ($lastLeaveCert && preg_match('/LC-\d{8}-(\d{3})/', $lastLeaveCert, $matches)) {
                $leaveCertSeq = intval($matches[1]) + 1;
            }
            $leaveCertificateNumber = "LC-{$dateStr}-" . str_pad($leaveCertSeq, 3, '0', STR_PAD_LEFT);
            
            $fareCertificateNumber = null;
            if ($request->fare_approved_amount > 0) {
                $lastFareCert = LeaveRequest::whereNotNull('fare_certificate_number')
                    ->whereDate('created_at', today())
                    ->where('fare_certificate_number', 'like', "FC-{$dateStr}-%")
                    ->orderBy('fare_certificate_number', 'desc')
                    ->value('fare_certificate_number');
                
                $fareCertSeq = 1;
                if ($lastFareCert && preg_match('/FC-\d{8}-(\d{3})/', $lastFareCert, $matches)) {
                    $fareCertSeq = intval($matches[1]) + 1;
                }
                $fareCertificateNumber = "FC-{$dateStr}-" . str_pad($fareCertSeq, 3, '0', STR_PAD_LEFT);
            }
            
            $leaveRequest->update([
                'status' => 'on_leave',
                'approval_letter_number' => $approvalLetterNumber,
                'approval_date' => $request->approval_date,
                'leave_certificate_number' => $leaveCertificateNumber,
                'fare_approved_amount' => $request->fare_approved_amount,
                'fare_certificate_number' => $fareCertificateNumber,
                'payment_voucher_number' => $paymentVoucherNumber,
                'payment_date' => $request->payment_date,
                'hr_processing_notes' => $request->additional_notes,
                'documents_processed_by' => $user->id,
                'documents_processed_at' => now(),
            ]);
            
            // Auto-generate petty cash request if fare amount > 0
            if ($request->fare_approved_amount > 0) {
                try {
                    $this->createPettyCashRequest($leaveRequest, $request->fare_approved_amount, $paymentVoucherNumber);
                } catch (\Exception $pcError) {
                    \Log::warning('Failed to create petty cash request: ' . $pcError->getMessage(), [
                        'request_id' => $id,
                        'trace' => $pcError->getTraceAsString()
                    ]);
                    // Don't fail the entire process if petty cash creation fails
                }
            }
            
            // Generate and store documents (non-blocking)
            try {
                $this->generateLeaveDocuments($leaveRequest, $user);
                
                // Log that approval letter is ready for generation
                \Log::info('Approval letter ready for PDF generation', [
                    'request_id' => $id,
                    'approval_letter_number' => $approvalLetterNumber
                ]);
            } catch (\Exception $docError) {
                \Log::warning('Failed to generate leave documents: ' . $docError->getMessage(), [
                    'request_id' => $id,
                    'trace' => $docError->getTraceAsString()
                ]);
                // Don't fail the entire process if document generation fails
            }
            
            // Reload leave request with relationships for notifications
            $leaveRequest->refresh();
            $leaveRequest->load(['employee', 'leaveType']);
            
            // Send notification to employee (non-blocking)
            try {
                $this->notificationService->notify(
                    $leaveRequest->employee_id,
                    "Your leave request has been processed. You are now on leave. Leave Certificate: {$leaveCertificateNumber}",
                    "/modules/hr/leave",
                    'Leave Documents Processed'
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to send notification after processing documents: ' . $e->getMessage());
            }
            
            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('leave_documents_processed', "Processed documents for leave request", $leaveRequest, [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'leave_certificate_number' => $leaveCertificateNumber,
                'fare_certificate_number' => $fareCertificateNumber,
                'fare_approved_amount' => $request->fare_approved_amount,
                'processed_by' => $user->name,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Documents processed successfully. Employee is now on leave.',
                'documents' => [
                    'leave_certificate_number' => $leaveCertificateNumber,
                    'fare_certificate_number' => $fareCertificateNumber,
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process documents: ' . $e->getMessage()
            ]);
        }
    }
    
    public function submitReturnForm(Request $request, $id)
    {
        $user = Auth::user();
        
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('employee_id', $user->id)
            ->where('status', 'on_leave')
            ->first();
        
        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found or not eligible for return processing.'
            ]);
        }
        
        // Ensure end_date is Carbon instance
        if (!$leaveRequest->end_date instanceof Carbon) {
            $leaveRequest->end_date = Carbon::parse($leaveRequest->end_date);
        }
        
        // Check if leave has actually ended (allow same day or after)
        if (Carbon::now()->format('Y-m-d') < $leaveRequest->end_date->format('Y-m-d')) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot submit return form before your leave end date (' . $leaveRequest->end_date->format('F j, Y') . ').'
            ]);
        }
        
        $request->validate([
            'actual_return_date' => [
                'required',
                'date',
                'after_or_equal:' . $leaveRequest->end_date->format('Y-m-d'),
                'before_or_equal:' . Carbon::parse($leaveRequest->end_date)->addDays(30)->format('Y-m-d') // Allow up to 30 days after end date
            ],
            'health_status' => 'required|in:excellent,good,fair,poor',
            'work_readiness' => 'required|in:fully_ready,partially_ready,needs_training,not_ready',
            'comments' => 'nullable|string|max:1000',
            'resumption_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'actual_return_date.after_or_equal' => 'Actual return date must be on or after your leave end date (' . $leaveRequest->end_date->format('F j, Y') . ').',
            'actual_return_date.before_or_equal' => 'Actual return date cannot be more than 30 days after your leave end date.',
            'health_status.required' => 'Please select your health status.',
            'health_status.in' => 'Invalid health status selected.',
            'work_readiness.required' => 'Please select your work readiness status.',
            'work_readiness.in' => 'Invalid work readiness status selected.',
            'resumption_certificate.mimes' => 'Resumption certificate must be a PDF, JPG, JPEG, or PNG file.',
            'resumption_certificate.max' => 'Resumption certificate must not exceed 2MB in size.',
        ]);
        
        DB::beginTransaction();
        try {
            $resumptionCertPath = null;
            if ($request->hasFile('resumption_certificate')) {
                $file = $request->file('resumption_certificate');
                $resumptionCertPath = $file->store('resumption_certs', 'public');
            }
            
            $leaveRequest->update([
                'status' => 'completed',
                'actual_return_date' => Carbon::parse($request->actual_return_date),
                'health_status' => $request->health_status,
                'work_readiness' => $request->work_readiness,
                'return_comments' => $request->comments,
                'resumption_certificate_path' => $resumptionCertPath,
                'return_submitted_at' => now(),
            ]);
            
            // Reload leave request with relationships for notifications
            $leaveRequest->refresh();
            $leaveRequest->load(['employee', 'leaveType']);
            
            // Send notification to HR (non-blocking)
            try {
                $hrUsers = User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['HR Officer', 'System Admin']);
                })->get();
                
                foreach ($hrUsers as $hrUser) {
                    $this->notificationService->notify(
                        $hrUser->id,
                        "Employee {$leaveRequest->employee->name} has returned from leave ({$leaveRequest->leaveType->name}). Health Status: " . ucfirst($request->health_status) . ", Work Readiness: " . str_replace('_', ' ', ucfirst($request->work_readiness)),
                        "/modules/hr/leave",
                        'Employee Returned from Leave'
                    );
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send HR notification after return: ' . $e->getMessage());
            }
            
            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('leave_return_submitted', "Submitted return form for leave request", $leaveRequest, [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'actual_return_date' => $request->actual_return_date,
                'health_status' => $request->health_status,
                'work_readiness' => $request->work_readiness,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Return form submitted successfully. Welcome back to work! Your leave has been marked as completed.',
                'return_date' => $request->actual_return_date,
                'status' => 'completed'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Return Form Submission Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit return form: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function cancelRequest($id)
    {
        $user = Auth::user();
        
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('employee_id', $user->id)
            ->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval'])
            ->first();
        
        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found or cannot be cancelled.'
            ]);
        }
        
        $oldStatus = $leaveRequest->status;
        $leaveRequest->update(['status' => 'cancelled']);
        
        // Log activity
        ActivityLogService::logCancelled($leaveRequest, "Leave request cancelled by employee", $user->name, null, [
            'old_status' => $oldStatus,
            'new_status' => 'cancelled',
            'employee_id' => $user->id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Leave request cancelled successfully.'
        ]);
    }
    
    // HR Management Functions
    public function getAllRequests(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ]);
        }
        
        $query = LeaveRequest::with([
            'employee:id,name,email,primary_department_id',
            'employee.primaryDepartment:id,name',
            'leaveType:id,name,description',
            'reviewer:id,name,email',
            'documentProcessor:id,name,email',
            'dependents:id,leave_request_id,name,relationship,fare_amount'
        ])
            ->selectRaw('leave_requests.*, 
                users.name as employee_name,
                departments.name as department_name,
                leave_types.name as leave_type_name,
                reviewers.name as reviewed_by_name,
                processors.name as documents_processed_by_name')
            ->join('users', 'leave_requests.employee_id', '=', 'users.id')
            ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
            ->leftJoin('departments', 'users.primary_department_id', '=', 'departments.id')
            ->leftJoin('users as reviewers', 'leave_requests.reviewed_by', '=', 'reviewers.id')
            ->leftJoin('users as processors', 'leave_requests.documents_processed_by', '=', 'processors.id');
        
        // Apply filters
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'current_year':
                    $query->whereYear('leave_requests.created_at', date('Y'));
                    break;
                case 'last_30_days':
                    $query->where('leave_requests.created_at', '>=', now()->subDays(30));
                    break;
            }
        }
        
        if ($request->has('department_filter') && $request->department_filter) {
            $query->where('users.primary_department_id', $request->department_filter);
        }
        
        if ($request->has('status_filter') && $request->status_filter) {
            $query->where('leave_requests.status', $request->status_filter);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->where('leave_requests.start_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('leave_requests.end_date', '<=', $request->date_to);
        }
        
        $requests = $query->orderBy('leave_requests.created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $requests,
            'requests' => $requests
        ]);
    }
    
    public function getAnalytics(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'HOD', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }
        
        $year = $request->input('year', date('Y'));
        $departmentId = $request->input('department_id');
        $leaveTypeId = $request->input('leave_type_id');
        
        // Base query builder
        $baseQuery = LeaveRequest::query();
        
        // Apply year filter
        $baseQuery->whereYear('created_at', $year);
        
        // Apply department filter
        if ($departmentId) {
            $baseQuery->whereHas('employee', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }
        
        // Apply leave type filter
        if ($leaveTypeId) {
            $baseQuery->where('leave_type_id', $leaveTypeId);
        }
        
        // Overall statistics
        $stats = (clone $baseQuery)->selectRaw('
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_requests,
            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_requests,
            SUM(CASE WHEN status IN ("pending_hr_review", "pending_hod_approval", "pending_ceo_approval", "approved_pending_docs", "on_leave") THEN 1 ELSE 0 END) as active_requests,
            AVG(total_days) as avg_leave_days,
            SUM(total_fare_approved) as total_fare_approved
        ')->first();
        
        // Monthly trend
        $monthlyTrend = (clone $baseQuery)->selectRaw('
            MONTHNAME(created_at) as month,
            COUNT(*) as request_count,
            SUM(CASE WHEN status = "completed" THEN total_days ELSE 0 END) as completed_days
        ')
        ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
        ->orderBy(DB::raw('MONTH(created_at)'))
        ->get();
        
        // Department statistics
        $deptStatsQuery = LeaveRequest::selectRaw('
            departments.name as department,
            COUNT(leave_requests.id) as request_count,
            AVG(leave_requests.total_days) as avg_days
        ')
        ->join('users', 'leave_requests.employee_id', '=', 'users.id')
        ->leftJoin('departments', 'users.primary_department_id', '=', 'departments.id')
        ->whereYear('leave_requests.created_at', $year);
        
        if ($departmentId) {
            $deptStatsQuery->where('users.primary_department_id', $departmentId);
        }
        
        if ($leaveTypeId) {
            $deptStatsQuery->where('leave_requests.leave_type_id', $leaveTypeId);
        }
        
        $deptStats = $deptStatsQuery->groupBy('departments.id', 'departments.name')
        ->orderByDesc('request_count')
        ->get();
        
        // Leave type statistics
        $typeStatsQuery = LeaveRequest::selectRaw('
            leave_types.name as leave_type,
            COUNT(*) as request_count,
            AVG(total_days) as avg_days
        ')
        ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
        ->whereYear('leave_requests.created_at', $year);
        
        if ($departmentId) {
            $typeStatsQuery->whereHas('employee', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }
        
        if ($leaveTypeId) {
            $typeStatsQuery->where('leave_requests.leave_type_id', $leaveTypeId);
        }
        
        $typeStats = $typeStatsQuery->groupBy('leave_types.id', 'leave_types.name')
        ->orderByDesc('request_count')
        ->get();
        
        // Status statistics
        $statusStats = (clone $baseQuery)->selectRaw('
            status,
            COUNT(*) as count
        ')
        ->groupBy('status')
        ->orderByDesc('count')
        ->get();
        
        // Employee-level statistics (top 10 employees with most leave requests)
        $employeeStats = LeaveRequest::selectRaw('
            users.id as employee_id,
            users.name as employee_name,
            departments.name as department_name,
            COUNT(leave_requests.id) as request_count,
            SUM(leave_requests.total_days) as total_days_taken,
            AVG(leave_requests.total_days) as avg_days_per_request,
            SUM(CASE WHEN leave_requests.status = "completed" THEN 1 ELSE 0 END) as completed_count
        ')
        ->join('users', 'leave_requests.employee_id', '=', 'users.id')
        ->leftJoin('departments', 'users.primary_department_id', '=', 'departments.id')
        ->whereYear('leave_requests.created_at', $year);
        
        if ($departmentId) {
            $employeeStats->where('users.primary_department_id', $departmentId);
        }
        
        if ($leaveTypeId) {
            $employeeStats->where('leave_requests.leave_type_id', $leaveTypeId);
        }
        
        $employeeStats = $employeeStats->groupBy('users.id', 'users.name', 'departments.name')
            ->orderByDesc('request_count')
            ->limit(20)
            ->get();
        
        // Monthly breakdown by status
        $monthlyStatusBreakdown = (clone $baseQuery)->selectRaw('
            MONTHNAME(created_at) as month,
            MONTH(created_at) as month_num,
            status,
            COUNT(*) as count,
            SUM(total_days) as total_days
        ')
        ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'), 'status')
        ->orderBy(DB::raw('MONTH(created_at)'))
        ->orderBy('status')
        ->get();
        
        // Top leave requesters
        $topRequesters = LeaveRequest::selectRaw('
            users.name as employee_name,
            departments.name as department_name,
            COUNT(*) as total_requests,
            SUM(total_days) as total_days
        ')
        ->join('users', 'leave_requests.employee_id', '=', 'users.id')
        ->leftJoin('departments', 'users.primary_department_id', '=', 'departments.id')
        ->whereYear('leave_requests.created_at', $year);
        
        if ($departmentId) {
            $topRequesters->where('users.primary_department_id', $departmentId);
        }
        
        if ($leaveTypeId) {
            $topRequesters->where('leave_requests.leave_type_id', $leaveTypeId);
        }
        
        $topRequesters = $topRequesters->groupBy('users.id', 'users.name', 'departments.name')
            ->orderByDesc('total_requests')
            ->limit(10)
            ->get();
        
        // Approval timeline statistics
        $approvalStats = LeaveRequest::selectRaw('
            AVG(DATEDIFF(COALESCE(updated_at, NOW()), created_at)) as avg_processing_days,
            MIN(DATEDIFF(COALESCE(updated_at, NOW()), created_at)) as min_processing_days,
            MAX(DATEDIFF(COALESCE(updated_at, NOW()), created_at)) as max_processing_days
        ')
        ->whereYear('created_at', $year);
        
        if ($departmentId) {
            $approvalStats->whereHas('employee', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }
        
        if ($leaveTypeId) {
            $approvalStats->where('leave_type_id', $leaveTypeId);
        }
        
        $approvalStats = $approvalStats->first();
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'monthly_trend' => $monthlyTrend,
            'dept_stats' => $deptStats,
            'type_stats' => $typeStats,
            'status_stats' => $statusStats,
            'employee_stats' => $employeeStats,
            'monthly_status_breakdown' => $monthlyStatusBreakdown,
            'top_requesters' => $topRequesters,
            'approval_stats' => $approvalStats,
            'filters' => [
                'year' => $year,
                'department_id' => $departmentId,
                'leave_type_id' => $leaveTypeId
            ]
        ]);
    }
    
    public function manageLeaveBalance(Request $request)
    {
        try {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                    'message' => 'Authorization Failed: Only HR Officers and System Admins can manage leave balances.'
            ], 403);
        }
        
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'financial_year' => 'required|integer|min:2020|max:2030',
            'total_days_allotted' => 'required|integer|min:0|max:365',
            'carry_forward_days' => 'required|integer|min:0|max:365',
            ], [
                'employee_id.required' => 'Please select an employee.',
                'employee_id.exists' => 'Selected employee does not exist.',
                'financial_year.required' => 'Financial year is required.',
                'financial_year.integer' => 'Financial year must be a valid year.',
                'total_days_allotted.required' => 'Total days allotted is required.',
                'total_days_allotted.integer' => 'Total days must be a number.',
                'total_days_allotted.max' => 'Total days cannot exceed 365 days.',
                'carry_forward_days.integer' => 'Carry forward days must be a number.',
                'carry_forward_days.max' => 'Carry forward days cannot exceed 365 days.',
            ]);
            
            // Verify employee exists and is active
            $employee = User::find($request->employee_id);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected employee not found.'
                ], 404);
            }
            
            if (!$employee->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot manage balance for inactive employee.'
                ], 422);
            }
        
        // Use provided leave_type_id or default to annual
        $leaveTypeId = $request->leave_type_id;
        if (!$leaveTypeId) {
            $annualType = LeaveType::where('name', 'like', '%annual%')->first();
            if (!$annualType) {
                return response()->json([
                    'success' => false,
                        'message' => 'Annual leave type not found. Please create it first before managing balances.'
                ], 404);
            }
            $leaveTypeId = $annualType->id;
        }
        
            // Verify leave type exists and is active
            $leaveType = LeaveType::find($leaveTypeId);
            if (!$leaveType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected leave type not found.'
                ], 404);
            }
            
            DB::beginTransaction();
        try {
            $balance = LeaveBalance::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'leave_type_id' => $leaveTypeId,
                    'financial_year' => $request->financial_year,
                ],
                [
                    'total_days_allotted' => $request->total_days_allotted,
                    'carry_forward_days' => $request->carry_forward_days ?? 0,
                ]
            );
            
                $balance->load([
                    'leaveType:id,name,description',
                    'employee:id,name,email'
                ]);
                
                DB::commit();
            
            // Log activity
            ActivityLogService::logAction('leave_balance_managed', "Managed leave balance for {$employee->name}", $balance, [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'leave_type_id' => $leaveTypeId,
                'financial_year' => $request->financial_year,
                'total_days_allotted' => $request->total_days_allotted,
                'carry_forward_days' => $request->carry_forward_days ?? 0,
            ]);
            
            return response()->json([
                'success' => true,
                    'message' => 'Leave balance updated successfully for ' . $employee->name . '.',
                'balance' => $balance
            ]);
        } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('manageLeaveBalance Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token']),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave balance. Please try again or contact support if the issue persists.'
            ], 500);
        }
    }
    
    public function getBalanceData(Request $request)
    {
        try {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                    'message' => 'Authorization Failed: Only HR Officers and System Admins can view balance data.'
                ], 403);
        }
        
        $year = $request->input('year', date('Y'));
        $departmentId = $request->input('department_id');
        $employeeId = $request->input('employee_id');
            
            // Validate year
            if ($year < 2020 || $year > 2030) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid financial year. Year must be between 2020 and 2030.'
                ], 422);
            }
        
        // Get annual leave type
        $annualType = LeaveType::where('name', 'like', '%annual%')->first();
        $annualTypeId = $annualType ? $annualType->id : null;
        
        // Start with all active employees
                $userQuery = User::selectRaw('
                    users.id as employee_id,
                    users.name as employee_name,
                    departments.name as department_name,
                COALESCE(leave_balances.total_days_allotted, 0) as total_days_allotted,
                COALESCE(leave_balances.days_taken, 0) as days_taken,
                COALESCE(leave_balances.carry_forward_days, 0) as carry_forward_days,
                leave_types.name as leave_type_name,
                    ? as financial_year
                ', [$year])
                ->leftJoin('departments', 'users.primary_department_id', '=', 'departments.id')
            ->leftJoin('leave_balances', function($join) use ($year, $annualTypeId) {
                $join->on('leave_balances.employee_id', '=', 'users.id')
                     ->where('leave_balances.financial_year', '=', $year);
                if ($annualTypeId) {
                    $join->where('leave_balances.leave_type_id', '=', $annualTypeId);
                }
            })
            ->leftJoin('leave_types', 'leave_balances.leave_type_id', '=', 'leave_types.id')
                ->where('users.is_active', true);
            
            if ($employeeId) {
                $userQuery->where('users.id', $employeeId);
            }
            
            if ($departmentId) {
                $userQuery->where('users.primary_department_id', $departmentId);
            }
            
            $balances = $userQuery->orderBy('users.name')->get();
        
        // Calculate remaining days for each balance
        $balances = $balances->map(function($balance) {
            $balance->remaining_days = ($balance->total_days_allotted ?? 0) - ($balance->days_taken ?? 0);
            return $balance;
        });
        
        return response()->json([
            'success' => true,
                'balances' => $balances,
                'data' => $balances, // Also include as 'data' for compatibility
                'count' => $balances->count(),
                'year' => $year
            ]);
        } catch (\Exception $e) {
            \Log::error('getBalanceData Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load balance data. Please try again.'
            ], 500);
        }
    }
    
    /**
     * New Leave Request Page
     */
    public function newRequest()
    {
        $user = Auth::user();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.hr.leave-new', compact('leaveTypes', 'user'));
    }

    /**
     * Balance Management Page
     */
    public function balanceManagement()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'HOD', 'CEO'])) {
            abort(403, 'You do not have permission to access balance management');
        }

        $departments = Department::orderBy('name')->get();
        $currentYear = now()->year;
        $years = range($currentYear - 2, $currentYear + 2);
        
        return view('modules.hr.leave-balance', compact('departments', 'years', 'currentYear', 'user'));
    }

    /**
     * Recommendations Management Page
     */
    public function recommendationsManagement()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'HOD', 'CEO'])) {
            abort(403, 'You do not have permission to access recommendations management');
        }

        $departments = Department::orderBy('name')->get();
        $employees = User::where('is_active', true)->orderBy('name')->get();
        $currentYear = now()->year;
        $years = range($currentYear - 2, $currentYear + 2);
        
        return view('modules.hr.leave-recommendations', compact('departments', 'employees', 'years', 'currentYear', 'user'));
    }

    /**
     * Analytics Page
     */
    public function analyticsPage()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'HOD', 'CEO'])) {
            abort(403, 'You do not have permission to access analytics');
        }

        $departments = Department::orderBy('name')->get();
        $leaveTypes = LeaveType::orderBy('name')->get();
        $currentYear = now()->year;
        $years = range($currentYear - 5, $currentYear);
        
        return view('modules.hr.leave-analytics', compact('departments', 'leaveTypes', 'years', 'currentYear', 'user'));
    }
    
    /**
     * HR: Get all leave types for management
     */
    public function leaveTypesIndex()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Only HR and System Admins can manage leave types');
        }

        $leaveTypes = LeaveType::withCount('leaveRequests')->orderBy('name')->get();

        // If AJAX request, return JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'leaveTypes' => $leaveTypes
            ]);
        }

        return view('modules.hr.leave-types', compact('leaveTypes'));
    }

    /**
     * HR: Get single leave type for editing
     */
    public function getLeaveType(LeaveType $leaveType)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'leaveType' => $leaveType
        ]);
    }

    /**
     * HR: Store new leave type
     */
    public function storeLeaveType(Request $request)
    {
        try {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                    'message' => 'Unauthorized: Only HR Officers and System Admins can create leave types.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'description' => 'nullable|string|max:1000',
            'max_days_per_year' => 'required|integer|min:0|max:365',
            'requires_approval' => 'nullable|in:0,1,true,false',
            'is_paid' => 'nullable|in:0,1,true,false',
            'is_active' => 'nullable|in:0,1,true,false',
            ], [
                'name.required' => 'Leave type name is required.',
                'name.unique' => 'A leave type with this name already exists.',
                'name.max' => 'Leave type name cannot exceed 255 characters.',
                'max_days_per_year.required' => 'Maximum days per year is required.',
                'max_days_per_year.integer' => 'Maximum days must be a number.',
                'max_days_per_year.min' => 'Maximum days cannot be negative.',
                'max_days_per_year.max' => 'Maximum days cannot exceed 365.',
            ]);

            DB::beginTransaction();
            try {
        $leaveType = LeaveType::create([
                    'name' => trim($request->name),
                    'description' => $request->description ? trim($request->description) : null,
            'max_days_per_year' => $request->max_days_per_year,
            'requires_approval' => (bool) ($request->requires_approval ?? true),
            'is_paid' => (bool) ($request->is_paid ?? true),
            'is_active' => (bool) ($request->is_active ?? true),
        ]);
                
                DB::commit();
                
                // Log activity
                ActivityLogService::logCreated($leaveType, "Created leave type: {$leaveType->name}", [
                    'name' => $leaveType->name,
                    'max_days_per_year' => $leaveType->max_days_per_year,
                    'requires_approval' => $leaveType->requires_approval,
                    'is_paid' => $leaveType->is_paid,
                ]);
                
                // Send SMS notification to HR team
                try {
                    $hrUsers = User::whereHas('roles', function($query) {
                        $query->whereIn('name', ['HR Officer', 'System Admin']);
                    })->where('is_active', true)->get();
                    
                    foreach ($hrUsers as $hrUser) {
                        $phone = $hrUser->phone ?? $hrUser->mobile ?? null;
                        if ($phone) {
                            try {
                                $this->notificationService->sendSMS(
                                    $phone,
                                    "New leave type '{$leaveType->name}' has been created by " . Auth::user()->name . ". Max days: {$leaveType->max_days_per_year}."
                                );
                            } catch (\Exception $smsException) {
                                \Log::warning('SMS sending failed for HR user: ' . $hrUser->id . ' - ' . $smsException->getMessage());
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send SMS notification for leave type creation: ' . $e->getMessage());
                }

        return response()->json([
            'success' => true,
                    'message' => 'Leave type "' . $leaveType->name . '" created successfully!',
            'leaveType' => $leaveType
        ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $errorMessages),
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Store Leave Type Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token']),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create leave type. Please try again.'
            ], 500);
        }
    }

    /**
     * HR: Update leave type
     */
    public function updateLeaveType(Request $request, LeaveType $leaveType)
    {
        try {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                    'message' => 'Unauthorized: Only HR Officers and System Admins can update leave types.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name,' . $leaveType->id,
            'description' => 'nullable|string|max:1000',
            'max_days_per_year' => 'required|integer|min:0|max:365',
            'requires_approval' => 'nullable|in:0,1,true,false',
            'is_paid' => 'nullable|in:0,1,true,false',
            'is_active' => 'nullable|in:0,1,true,false',
            ], [
                'name.required' => 'Leave type name is required.',
                'name.unique' => 'A leave type with this name already exists.',
                'name.max' => 'Leave type name cannot exceed 255 characters.',
                'max_days_per_year.required' => 'Maximum days per year is required.',
                'max_days_per_year.integer' => 'Maximum days must be a number.',
                'max_days_per_year.min' => 'Maximum days cannot be negative.',
                'max_days_per_year.max' => 'Maximum days cannot exceed 365.',
            ]);

            DB::beginTransaction();
            try {
        $leaveType->update([
                    'name' => trim($request->name),
                    'description' => $request->description ? trim($request->description) : null,
            'max_days_per_year' => $request->max_days_per_year,
            'requires_approval' => isset($request->requires_approval) ? (bool) $request->requires_approval : $leaveType->requires_approval,
            'is_paid' => isset($request->is_paid) ? (bool) $request->is_paid : $leaveType->is_paid,
            'is_active' => isset($request->is_active) ? (bool) $request->is_active : $leaveType->is_active,
        ]);
                
                DB::commit();
                
                // Log activity
                $oldValues = array_intersect_key($leaveType->getOriginal(), $leaveType->getChanges());
                ActivityLogService::logUpdated($leaveType, $oldValues, $leaveType->getChanges(), "Updated leave type: {$leaveType->name}", [
                    'name' => $leaveType->name,
                    'max_days_per_year' => $leaveType->max_days_per_year,
                    'is_active' => $leaveType->is_active,
                ]);
                
                // Send SMS notification to HR team
                try {
                    $hrUsers = User::whereHas('roles', function($query) {
                        $query->whereIn('name', ['HR Officer', 'System Admin']);
                    })->where('is_active', true)->get();
                    
                    foreach ($hrUsers as $hrUser) {
                        $phone = $hrUser->phone ?? $hrUser->mobile ?? null;
                        if ($phone) {
                            try {
                                $this->notificationService->sendSMS(
                                    $phone,
                                    "Leave type '{$leaveType->name}' has been updated by " . Auth::user()->name . ". Status: " . ($leaveType->is_active ? 'Active' : 'Inactive') . "."
                                );
                            } catch (\Exception $smsException) {
                                \Log::warning('SMS sending failed for HR user: ' . $hrUser->id . ' - ' . $smsException->getMessage());
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send SMS notification for leave type update: ' . $e->getMessage());
                }

        return response()->json([
            'success' => true,
                    'message' => 'Leave type "' . $leaveType->name . '" updated successfully!',
                    'leaveType' => $leaveType->fresh()
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $errorMessages),
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Update Leave Type Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'leave_type_id' => $leaveType->id,
                'request_data' => $request->except(['_token']),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave type. Please try again.'
            ], 500);
        }
    }

    /**
     * HR: Delete leave type
     */
    public function destroyLeaveType(LeaveType $leaveType)
    {
        try {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                    'message' => 'Unauthorized: Only HR Officers and System Admins can delete leave types.'
            ], 403);
        }

        // Check if leave type has any requests
            $requestCount = $leaveType->leaveRequests()->count();
            if ($requestCount > 0) {
            return response()->json([
                'success' => false,
                    'message' => "Cannot delete leave type with {$requestCount} existing leave request(s). Please deactivate it instead or reassign the requests."
            ], 422);
        }

            DB::beginTransaction();
            try {
                $leaveTypeName = $leaveType->name;
        $leaveType->delete();
                DB::commit();
                
                // Send SMS notification to HR team
                try {
                    $hrUsers = User::whereHas('roles', function($query) {
                        $query->whereIn('name', ['HR Officer', 'System Admin']);
                    })->where('is_active', true)->get();
                    
                    foreach ($hrUsers as $hrUser) {
                        $phone = $hrUser->phone ?? $hrUser->mobile ?? null;
                        if ($phone) {
                            try {
                                $this->notificationService->sendSMS(
                                    $phone,
                                    "Leave type '{$leaveTypeName}' has been deleted by " . Auth::user()->name . "."
                                );
                            } catch (\Exception $smsException) {
                                \Log::warning('SMS sending failed for HR user: ' . $hrUser->id . ' - ' . $smsException->getMessage());
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send SMS notification for leave type deletion: ' . $e->getMessage());
                }

        return response()->json([
            'success' => true,
                    'message' => 'Leave type "' . $leaveTypeName . '" deleted successfully!'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Delete Leave Type Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'leave_type_id' => $leaveType->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leave type. Please try again.'
            ], 500);
        }
    }

    public function manageRecommendations(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ]);
        }
        
        // Handle list action
        if ($request->action === 'list') {
            try {
                $year = $request->input('financial_year', date('Y'));
                $employeeId = $request->input('employee_id');
                $departmentId = $request->input('department_id');
                
                // Get all active employees
                $userQuery = User::with(['primaryDepartment'])
                    ->where('users.is_active', true);
                
                if ($employeeId) {
                    $userQuery->where('users.id', $employeeId);
                }
                
                if ($departmentId) {
                    $userQuery->where('users.primary_department_id', $departmentId);
                }
                
                $employees = $userQuery->orderBy('users.name')->get();
                
                // Get all recommendations for the selected year
                $recommendationQuery = LeaveRecommendation::where('financial_year', $year)
                    ->where('status', 'approved');
                
                if ($employeeId) {
                    $recommendationQuery->where('employee_id', $employeeId);
                } else {
                    // Get employee IDs for filtering
                    $employeeIds = $employees->pluck('id')->toArray();
                    if (!empty($employeeIds)) {
                        $recommendationQuery->whereIn('employee_id', $employeeIds);
                    }
                }
                
                $allRecommendations = $recommendationQuery->get()->groupBy('employee_id');
                
                // Build response
                $recommendations = [];
                $employeeRecommendations = [];
                
                foreach ($employees as $employee) {
                    $empId = $employee->id;
                    $empRecommendations = $allRecommendations->get($empId, collect());
                    
                    $employeeRecommendations[$empId] = [
                        'employee_id' => $empId,
                        'employee_name' => $employee->name,
                        'department_name' => $employee->primaryDepartment ? $employee->primaryDepartment->name : null,
                        'recommendations' => []
                    ];
                    
                    if ($empRecommendations->isEmpty()) {
                        // Employee has no recommendations - add placeholder entry
                        $recommendations[] = [
                            'id' => null,
                            'employee_id' => $empId,
                            'employee_name' => $employee->name,
                            'department_name' => $employee->primaryDepartment ? $employee->primaryDepartment->name : null,
                            'recommended_start_date' => null,
                            'recommended_end_date' => null,
                            'financial_year' => $year,
                            'status' => null,
                            'notes' => null,
                            'has_recommendations' => false
                        ];
                    } else {
                        // Employee has recommendations - add each one
                        foreach ($empRecommendations as $rec) {
                            $recData = [
                                'id' => $rec->id,
                                'employee_id' => $empId,
                                'employee_name' => $employee->name,
                                'department_name' => $employee->primaryDepartment ? $employee->primaryDepartment->name : null,
                                'recommended_start_date' => $rec->recommended_start_date,
                                'recommended_end_date' => $rec->recommended_end_date,
                                'financial_year' => $rec->financial_year,
                                'status' => $rec->status,
                                'notes' => $rec->notes
                            ];
                            
                            $recommendations[] = $recData;
                            $employeeRecommendations[$empId]['recommendations'][] = [
                                'id' => $rec->id,
                                'recommended_start_date' => $rec->recommended_start_date,
                                'recommended_end_date' => $rec->recommended_end_date,
                                'financial_year' => $rec->financial_year,
                                'status' => $rec->status,
                                'notes' => $rec->notes
                            ];
                        }
                    }
                }
            
            return response()->json([
                'success' => true,
                    'recommendations' => $recommendations,
                    'data' => $recommendations, // Also include as 'data' for compatibility
                    'employees' => $employeeRecommendations // Grouped by employee
                ]);
            } catch (\Exception $e) {
                \Log::error('List Recommendations Error: ' . $e->getMessage(), [
                    'user_id' => Auth::id(),
                    'request_data' => $request->except(['_token']),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load recommendations: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Handle auto-assign action
        if ($request->action === 'auto_assign') {
            try {
            $year = $request->input('financial_year', date('Y'));
                
                // Validate year
                if ($year < 2020 || $year > 2030) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid financial year. Year must be between 2020 and 2030.'
                    ], 422);
                }
                
            // Group employees by department for sequential assignment
            $employeesByDept = User::where('is_active', true)
                ->whereNotNull('primary_department_id')
                ->with('primaryDepartment')
                ->orderBy('primary_department_id')
                ->orderBy('name')
                ->get()
                ->groupBy('primary_department_id');
                
                if ($employeesByDept->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active employees with departments found to assign recommendations.'
                    ], 404);
                }
                
            $created = 0;
                $skipped = 0;
                $errors = [];
            
                DB::beginTransaction();
                try {
            // Process each department separately
            foreach ($employeesByDept as $departmentId => $departmentEmployees) {
                $deptEmployees = $departmentEmployees->values()->all();
                $deptEmployeeCount = count($deptEmployees);
                $departmentName = $deptEmployees[0]->primaryDepartment ? $deptEmployees[0]->primaryDepartment->name : 'Unknown';
                
                // Track assigned dates for this department to ensure sequential assignment
                $deptAssignedDates = []; // Track end dates to assign next employee after previous ends
                
                // Process each employee in the department sequentially (numbered 1, 2, 3, etc.)
                foreach ($deptEmployees as $index => $employee) {
                    $employeeNumber = $index + 1;
                    
                    try {
                        // Check if employee already has 3 recommendations for this year
                        $existingCount = LeaveRecommendation::where('employee_id', $employee->id)
                            ->where('financial_year', $year)
                            ->where('status', 'approved')
                            ->count();
                        
                        if ($existingCount >= 3) {
                                $skipped++;
                                continue;
                            }
                
                        // Get optimal periods for this employee
                        $optimalPeriods = $this->getOptimalLeavePeriods($employee->id, $year);
                        
                        if (empty($optimalPeriods)) {
                            // If no optimal periods, create a default recommendation
                            // Start from beginning of year or after last assigned date in department
                            if (!empty($deptAssignedDates)) {
                                $baseStartDate = Carbon::parse(max($deptAssignedDates));
                            } else {
                                $baseStartDate = Carbon::create($year, 1, 1);
                            }
                            $defaultStartDate = $baseStartDate->copy()->addDays(1); // Start day after previous ends
                            $defaultEndDate = $defaultStartDate->copy()->addDays(13); // 14 days leave
                            
                            // Ensure dates are within the year
                            if ($defaultEndDate->year > $year) {
                                $defaultEndDate = Carbon::create($year, 12, 31);
                                $defaultStartDate = $defaultEndDate->copy()->subDays(13);
                            }
                            
                            $optimalPeriods = [[
                                'start_date' => $defaultStartDate->format('Y-m-d'),
                                'end_date' => $defaultEndDate->format('Y-m-d'),
                                'type' => 'sequential',
                                'period' => 'Sequential Assignment',
                                'reason' => "Auto-assigned as employee #{$employeeNumber} of {$deptEmployeeCount} in {$departmentName} department"
                            ]];
                        }
                        
                        // Try to assign recommendation from optimal periods
                        $assigned = false;
                foreach ($optimalPeriods as $period) {
                            if ($assigned) break;
                            
                                // Validate period dates
                            $startDateStr = $period['start_date'] ?? $period->start_date ?? null;
                            $endDateStr = $period['end_date'] ?? $period->end_date ?? null;
                            
                            if (empty($startDateStr) || empty($endDateStr)) {
                                    continue;
                                }
                                
                            try {
                                $startDate = Carbon::parse($startDateStr);
                                $endDate = Carbon::parse($endDateStr);
                            } catch (\Exception $e) {
                                continue;
                            }
                                
                                if ($startDate->greaterThan($endDate)) {
                                    continue;
                                }
                                
                            $startDateFormatted = $startDate->format('Y-m-d');
                            $endDateFormatted = $endDate->format('Y-m-d');
                            
                            // For sequential assignment: if previous employee assigned, start after their end date
                            if (!empty($deptAssignedDates)) {
                                $lastEndDateStr = max($deptAssignedDates);
                                $lastEndDate = Carbon::parse($lastEndDateStr);
                                if ($startDate->lessThanOrEqualTo($lastEndDate)) {
                                    // Adjust start date to be after last employee's end date
                                    $startDate = $lastEndDate->copy()->addDay();
                                    $endDate = $startDate->copy()->addDays($startDate->diffInDays(Carbon::parse($endDateStr)));
                                    $startDateFormatted = $startDate->format('Y-m-d');
                                    $endDateFormatted = $endDate->format('Y-m-d');
                                }
                                }
                                
                    // Check if already has recommendation for this period
                    $existing = LeaveRecommendation::where('employee_id', $employee->id)
                        ->where('financial_year', $year)
                                ->where('recommended_start_date', $startDateFormatted)
                                ->where('recommended_end_date', $endDateFormatted)
                        ->first();
                    
                            if ($existing) {
                                continue;
                            }
                            
                            // Check if dates overlap with other employees in same department (max 2 at a time)
                            $overlappingCount = LeaveRecommendation::whereHas('employee', function($q) use ($departmentId) {
                                    $q->where('primary_department_id', $departmentId);
                                })
                                        ->where('financial_year', $year)
                                        ->where('status', 'approved')
                                ->where(function($q) use ($startDateFormatted, $endDateFormatted) {
                                    $q->whereBetween('recommended_start_date', [$startDateFormatted, $endDateFormatted])
                                      ->orWhereBetween('recommended_end_date', [$startDateFormatted, $endDateFormatted])
                                      ->orWhere(function($q2) use ($startDateFormatted, $endDateFormatted) {
                                          $q2->where('recommended_start_date', '<=', $startDateFormatted)
                                             ->where('recommended_end_date', '>=', $endDateFormatted);
                                      });
                                })
                                        ->count();
                                    
                            // Also check active leave requests
                            $overlappingRequests = LeaveRequest::whereHas('employee', function($q) use ($departmentId) {
                                    $q->where('primary_department_id', $departmentId);
                                })
                                ->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval', 'approved_pending_docs', 'on_leave'])
                                ->where(function($q) use ($startDateFormatted, $endDateFormatted) {
                                    $q->whereBetween('start_date', [$startDateFormatted, $endDateFormatted])
                                      ->orWhereBetween('end_date', [$startDateFormatted, $endDateFormatted])
                                      ->orWhere(function($q2) use ($startDateFormatted, $endDateFormatted) {
                                          $q2->where('start_date', '<=', $startDateFormatted)
                                             ->where('end_date', '>=', $endDateFormatted);
                                      });
                                })
                                ->count();
                            
                            $totalOverlapping = $overlappingCount + $overlappingRequests;
                            
                            // Max 2 staff per department can overlap
                            if ($totalOverlapping >= 2) {
                                continue; // Try next period
                            }
                            
                            // Generate automatic reason/notes
                            $reason = $period['reason'] ?? $period->reason ?? '';
                            if (empty($reason)) {
                                $reason = "Auto-assigned recommendation for employee #{$employeeNumber} of {$deptEmployeeCount} in {$departmentName} department. ";
                                if ($employeeNumber > 1) {
                                    $reason .= "Sequentially assigned to ensure department coverage.";
                                } else {
                                    $reason .= "Based on optimal leave periods and department workload.";
                                }
                            } else {
                                $reason .= " (Employee #{$employeeNumber} of {$deptEmployeeCount} in {$departmentName})";
                            }
                            
                            // Create recommendation with automatic reason
                            // Try with notes first, if that fails (column doesn't exist), try without notes
                            try {
                        LeaveRecommendation::create([
                            'employee_id' => $employee->id,
                                    'recommended_start_date' => $startDateFormatted,
                                    'recommended_end_date' => $endDateFormatted,
                            'financial_year' => $year,
                            'status' => 'approved',
                                    'notes' => $reason,
                                ]);
                            } catch (\Exception $createError) {
                                // If notes column doesn't exist, create without notes
                                if (strpos($createError->getMessage(), 'notes') !== false || strpos($createError->getMessage(), 'Column not found') !== false) {
                                    try {
                                        LeaveRecommendation::create([
                                            'employee_id' => $employee->id,
                                            'recommended_start_date' => $startDateFormatted,
                                            'recommended_end_date' => $endDateFormatted,
                                            'financial_year' => $year,
                                            'status' => 'approved',
                                        ]);
                                    } catch (\Exception $retryError) {
                                        throw $retryError; // Re-throw if still fails
                                    }
                                } else {
                                    throw $createError; // Re-throw if it's a different error
                                }
                            }
                            
                            // Track this assignment for sequential processing
                            $deptAssignedDates[] = $endDateFormatted;
                        $created++;
                            $assigned = true;
                        }
                        
                        if (!$assigned) {
                            // If no optimal periods worked, try to create a default recommendation anyway (force assignment)
                            try {
                                if (!empty($deptAssignedDates)) {
                                    $baseStartDate = Carbon::parse(max($deptAssignedDates));
                                } else {
                                    $baseStartDate = Carbon::create($year, 1, 1);
                                }
                                $defaultStartDate = $baseStartDate->copy()->addDays(1);
                                $defaultEndDate = $defaultStartDate->copy()->addDays(13);
                                
                                // Ensure dates are within the year
                                if ($defaultEndDate->year > $year) {
                                    $defaultEndDate = Carbon::create($year, 12, 31);
                                    $defaultStartDate = $defaultEndDate->copy()->subDays(13);
                                }
                                
                                $startDateFormatted = $defaultStartDate->format('Y-m-d');
                                $endDateFormatted = $defaultEndDate->format('Y-m-d');
                                
                                // Check if already exists
                                $existing = LeaveRecommendation::where('employee_id', $employee->id)
                                    ->where('financial_year', $year)
                                    ->where('recommended_start_date', $startDateFormatted)
                                    ->where('recommended_end_date', $endDateFormatted)
                                    ->first();
                                
                                if (!$existing) {
                                    $reason = "Auto-assigned default recommendation for employee #{$employeeNumber} of {$deptEmployeeCount} in {$departmentName} department.";
                                    
                                    try {
                                        LeaveRecommendation::create([
                                            'employee_id' => $employee->id,
                                            'recommended_start_date' => $startDateFormatted,
                                            'recommended_end_date' => $endDateFormatted,
                                            'financial_year' => $year,
                                            'status' => 'approved',
                                            'notes' => $reason,
                                        ]);
                                        $deptAssignedDates[] = $endDateFormatted;
                                        $created++;
                                        $assigned = true;
                                    } catch (\Exception $createError) {
                                        // If notes column doesn't exist, create without notes
                                        if (strpos($createError->getMessage(), 'notes') !== false || strpos($createError->getMessage(), 'Column not found') !== false) {
                                            LeaveRecommendation::create([
                                                'employee_id' => $employee->id,
                                                'recommended_start_date' => $startDateFormatted,
                                                'recommended_end_date' => $endDateFormatted,
                                                'financial_year' => $year,
                                                'status' => 'approved',
                                            ]);
                                            $deptAssignedDates[] = $endDateFormatted;
                                            $created++;
                                            $assigned = true;
                                    } else {
                                        $skipped++;
                                        }
                                    }
                                } else {
                                    $skipped++;
                                }
                            } catch (\Exception $defaultError) {
                                    $skipped++;
                                }
                            }
                        } catch (\Exception $e) {
                        // Try to create a basic recommendation even on error (force assignment)
                        try {
                            if (!empty($deptAssignedDates)) {
                                $baseStartDate = Carbon::parse(max($deptAssignedDates));
                            } else {
                                $baseStartDate = Carbon::create($year, 1, 1);
                            }
                            $defaultStartDate = $baseStartDate->copy()->addDays(1);
                            $defaultEndDate = $defaultStartDate->copy()->addDays(13);
                            
                            if ($defaultEndDate->year > $year) {
                                $defaultEndDate = Carbon::create($year, 12, 31);
                                $defaultStartDate = $defaultEndDate->copy()->subDays(13);
                            }
                            
                            $startDateFormatted = $defaultStartDate->format('Y-m-d');
                            $endDateFormatted = $defaultEndDate->format('Y-m-d');
                            
                            $existing = LeaveRecommendation::where('employee_id', $employee->id)
                                ->where('financial_year', $year)
                                ->where('recommended_start_date', $startDateFormatted)
                                ->where('recommended_end_date', $endDateFormatted)
                                ->first();
                            
                            if (!$existing) {
                                try {
                                    LeaveRecommendation::create([
                                        'employee_id' => $employee->id,
                                        'recommended_start_date' => $startDateFormatted,
                                        'recommended_end_date' => $endDateFormatted,
                                        'financial_year' => $year,
                                        'status' => 'approved',
                                    ]);
                                    $deptAssignedDates[] = $endDateFormatted;
                                    $created++;
                                } catch (\Exception $fallbackError) {
                                    // If notes column issue, try without notes
                                    if (strpos($fallbackError->getMessage(), 'notes') !== false || strpos($fallbackError->getMessage(), 'Column not found') !== false) {
                                        try {
                                            LeaveRecommendation::create([
                                                'employee_id' => $employee->id,
                                                'recommended_start_date' => $startDateFormatted,
                                                'recommended_end_date' => $endDateFormatted,
                                                'financial_year' => $year,
                                                'status' => 'approved',
                                            ]);
                                            $deptAssignedDates[] = $endDateFormatted;
                                            $created++;
                                        } catch (\Exception $finalError) {
                                            $errors[] = "Error processing employee {$employee->name} (#{$employeeNumber} in {$departmentName}): " . $e->getMessage();
                            \Log::warning("Error auto-assigning recommendation for employee {$employee->id}: " . $e->getMessage());
                                        }
                                    } else {
                                        $errors[] = "Error processing employee {$employee->name} (#{$employeeNumber} in {$departmentName}): " . $e->getMessage();
                                        \Log::warning("Error auto-assigning recommendation for employee {$employee->id}: " . $e->getMessage());
                                    }
                                }
                            }
                        } catch (\Exception $fallbackError) {
                            $errors[] = "Error processing employee {$employee->name} (#{$employeeNumber} in {$departmentName}): " . $e->getMessage();
                            \Log::warning("Error auto-assigning recommendation for employee {$employee->id}: " . $e->getMessage());
                        }
                    }
                        }
                    }
                    
                    DB::commit();
                    
                    $message = "Successfully auto-assigned {$created} recommendation(s).";
                    if ($skipped > 0) {
                        $message .= " {$skipped} recommendation(s) were skipped (already exist or limit reached).";
                    }
                    if (!empty($errors)) {
                        $message .= " Some errors occurred: " . implode('; ', array_slice($errors, 0, 3));
                    }
            
            return response()->json([
                'success' => true,
                        'message' => $message,
                        'created' => $created,
                        'skipped' => $skipped,
                        'errors_count' => count($errors)
                    ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            } catch (\Exception $e) {
                \Log::error('Auto-assign Recommendations Error: ' . $e->getMessage(), [
                    'user_id' => Auth::id(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to auto-assign recommendations: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Handle bulk remove action
        if ($request->action === 'bulk_remove') {
            try {
            $recommendationIds = $request->input('recommendation_ids', []);
                
                if (empty($recommendationIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No recommendations selected for deletion.'
                    ], 422);
                }
                
                // Validate that all IDs exist
                $existingCount = LeaveRecommendation::whereIn('id', $recommendationIds)->count();
                
                if ($existingCount === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No valid recommendations found to delete.'
                    ], 404);
                }
                
                DB::beginTransaction();
                try {
            $deleted = LeaveRecommendation::whereIn('id', $recommendationIds)->delete();
                    DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deleted} recommendation(s)."
            ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            } catch (\Exception $e) {
                \Log::error('Bulk Remove Recommendations Error: ' . $e->getMessage(), [
                    'user_id' => Auth::id(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete recommendations: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Handle get action (for viewing/editing single recommendation)
        if ($request->action === 'get') {
            try {
                $recommendationId = $request->input('recommendation_id');
                
                if (!$recommendationId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Recommendation ID is required.'
                    ], 422);
                }
                
                $recommendation = LeaveRecommendation::with(['employee.primaryDepartment'])
                    ->find($recommendationId);
                
                if (!$recommendation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Recommendation not found.'
                    ], 404);
                }
                
                return response()->json([
                    'success' => true,
                    'recommendation' => [
                        'id' => $recommendation->id,
                        'employee_id' => $recommendation->employee_id,
                        'employee_name' => $recommendation->employee ? $recommendation->employee->name : null,
                        'department_name' => $recommendation->employee && $recommendation->employee->primaryDepartment 
                            ? $recommendation->employee->primaryDepartment->name 
                            : null,
                        'recommended_start_date' => $recommendation->recommended_start_date,
                        'recommended_end_date' => $recommendation->recommended_end_date,
                        'financial_year' => $recommendation->financial_year,
                        'status' => $recommendation->status,
                        'notes' => $recommendation->notes,
                        'created_at' => $recommendation->created_at
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::error('Get Recommendation Error: ' . $e->getMessage(), [
                    'user_id' => Auth::id(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load recommendation: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Handle update action
        if ($request->action === 'update') {
            try {
                $request->validate([
                    'recommendation_id' => 'required|exists:leave_recommendations,id',
                    'employee_id' => 'required|exists:users,id',
                    'start_date' => 'required|date|after_or_equal:' . date('Y-01-01'),
                    'end_date' => 'required|date|after:start_date',
                    'financial_year' => 'required|integer|min:2020|max:2030',
                ], [
                    'recommendation_id.required' => 'Recommendation ID is required.',
                    'recommendation_id.exists' => 'Recommendation not found.',
                    'employee_id.required' => 'Please select an employee.',
                    'employee_id.exists' => 'Selected employee does not exist.',
                    'start_date.required' => 'Start date is required.',
                    'start_date.date' => 'Start date must be a valid date.',
                    'end_date.required' => 'End date is required.',
                    'end_date.date' => 'End date must be a valid date.',
                    'end_date.after' => 'End date must be after start date.',
                    'financial_year.required' => 'Financial year is required.',
                    'financial_year.integer' => 'Financial year must be a valid year.',
                ]);
                
                $recommendation = LeaveRecommendation::find($request->recommendation_id);
                
                if (!$recommendation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Recommendation not found.'
                    ], 404);
                }
                
                // Verify employee exists and is active
                $employee = User::find($request->employee_id);
                if (!$employee || !$employee->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot update recommendation for inactive employee.'
                    ], 422);
                }
                
                // Validate dates are within the financial year
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                
                if ($startDate->year != $request->financial_year && $endDate->year != $request->financial_year) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Recommendation dates must be within the selected financial year.'
                    ], 422);
                }
                
                // Check if another recommendation already exists for this period (excluding current)
                $existing = LeaveRecommendation::where('employee_id', $request->employee_id)
                    ->where('financial_year', $request->financial_year)
                    ->where('recommended_start_date', $startDate->format('Y-m-d'))
                    ->where('recommended_end_date', $endDate->format('Y-m-d'))
                    ->where('id', '!=', $request->recommendation_id)
                    ->first();
                
                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A recommendation for this period already exists for this employee.'
                    ], 422);
                }
                
                DB::beginTransaction();
                try {
                    $recommendation->update([
                        'employee_id' => $request->employee_id,
                        'recommended_start_date' => $startDate->format('Y-m-d'),
                        'recommended_end_date' => $endDate->format('Y-m-d'),
                        'financial_year' => $request->financial_year,
                        'notes' => $request->input('notes'),
                    ]);
                    
                    DB::commit();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Recommendation updated successfully for ' . $employee->name . '.',
                        'recommendation' => $recommendation
                    ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Update Recommendation Error: ' . $e->getMessage(), [
                        'user_id' => Auth::id(),
                        'request_data' => $request->except(['_token']),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', array_map(function($errors) {
                        return is_array($errors) ? implode(', ', $errors) : $errors;
                    }, $e->errors())),
                    'errors' => $e->errors()
                ], 422);
            } catch (\Exception $e) {
                \Log::error('Update Recommendation Error: ' . $e->getMessage(), [
                    'user_id' => Auth::id(),
                    'request_data' => $request->except(['_token']),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update recommendation: ' . $e->getMessage()
                ], 500);
            }
        }
        
        try {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
                'start_date' => 'required|date|after_or_equal:' . date('Y-01-01'),
            'end_date' => 'required|date|after:start_date',
            'financial_year' => 'required|integer|min:2020|max:2030',
            'action' => 'required|in:add,remove',
            'recommendation_id' => 'nullable|required_if:action,remove|exists:leave_recommendations,id',
            ], [
                'employee_id.required' => 'Please select an employee.',
                'employee_id.exists' => 'Selected employee does not exist.',
                'start_date.required' => 'Start date is required.',
                'start_date.date' => 'Start date must be a valid date.',
                'end_date.required' => 'End date is required.',
                'end_date.date' => 'End date must be a valid date.',
                'end_date.after' => 'End date must be after start date.',
                'financial_year.required' => 'Financial year is required.',
                'financial_year.integer' => 'Financial year must be a valid year.',
        ]);
        
        if ($request->action === 'add') {
                // Verify employee exists and is active
                $employee = User::find($request->employee_id);
                if (!$employee || !$employee->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot add recommendation for inactive employee.'
                    ], 422);
                }
                
                // Validate dates are within the financial year
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                
                if ($startDate->year != $request->financial_year && $endDate->year != $request->financial_year) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Recommendation dates must be within the selected financial year.'
                    ], 422);
                }
                
                // Check if recommendation already exists for this period
                $existing = LeaveRecommendation::where('employee_id', $request->employee_id)
                    ->where('financial_year', $request->financial_year)
                    ->where('recommended_start_date', $startDate->format('Y-m-d'))
                    ->where('recommended_end_date', $endDate->format('Y-m-d'))
                    ->first();
                
                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A recommendation for this period already exists for this employee.'
                    ], 422);
                }
                
                // Check if employee already has 3 recommendations for this year
            $existingCount = LeaveRecommendation::where('employee_id', $request->employee_id)
                ->where('financial_year', $request->financial_year)
                ->where('status', 'approved')
                ->count();
            
            if ($existingCount >= 3) {
                return response()->json([
                    'success' => false,
                        'message' => 'Maximum 3 recommended periods per employee per year. Please remove an existing recommendation first.'
                    ], 422);
            }
            
                DB::beginTransaction();
                try {
                    // Ensure dates are properly formatted
                    $startDateFormatted = $startDate->format('Y-m-d');
                    $endDateFormatted = $endDate->format('Y-m-d');
                    
                    // Validate dates are not null
                    if (empty($startDateFormatted) || empty($endDateFormatted)) {
                        throw new \Exception('Invalid date format. Please check your date inputs.');
                    }
                    
                    $recommendation = LeaveRecommendation::create([
                'employee_id' => $request->employee_id,
                        'recommended_start_date' => $startDateFormatted,
                        'recommended_end_date' => $endDateFormatted,
                'financial_year' => $request->financial_year,
                'status' => 'approved',
            ]);
                    
                    DB::commit();
            
            return response()->json([
                'success' => true,
                        'message' => 'Recommendation added successfully for ' . $employee->name . '.',
                        'recommendation' => $recommendation
            ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Create Recommendation Error: ' . $e->getMessage(), [
                        'user_id' => Auth::id(),
                        'request_data' => $request->except(['_token']),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
        } else {
                // Remove action
                $recommendation = LeaveRecommendation::where('id', $request->recommendation_id)
                ->where('employee_id', $request->employee_id)
                    ->first();
                
                if (!$recommendation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Recommendation not found or you do not have permission to remove it.'
                    ], 404);
                }
                
                DB::beginTransaction();
                try {
                    $recommendation->delete();
                    DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Recommendation removed successfully.'
            ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_map(function($errors) {
                    return is_array($errors) ? implode(', ', $errors) : $errors;
                }, $e->errors())),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Manage Recommendations Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token']),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process recommendation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Helper methods
    private function getOptimalLeavePeriods($employeeId, $year)
    {
        $employee = User::find($employeeId);
        if (!$employee || !$employee->primary_department_id) {
            return [];
        }
        
        $optimalPeriods = [];
        
        try {
            // Strategy 1: Check historical low-usage periods
            $lowMonths = LeaveRequest::selectRaw('MONTH(start_date) as month, COUNT(*) as request_count')
                ->join('users', 'leave_requests.employee_id', '=', 'users.id')
                ->where('users.primary_department_id', $employee->primary_department_id)
                ->whereYear('leave_requests.start_date', $year)
                ->whereIn('leave_requests.status', ['completed', 'on_leave'])
                ->groupBy(DB::raw('MONTH(start_date)'))
                ->orderBy('request_count', 'asc')
                ->limit(3)
                ->get();
            
            foreach ($lowMonths as $monthData) {
                $month = $monthData->month;
                $startDate = date('Y-m-d', strtotime("{$year}-{$month}-01"));
                $endDate = date('Y-m-t', strtotime("{$year}-{$month}-01"));
                
                $optimalPeriods[] = [
                    'type' => 'low_season',
                    'period' => date('F', mktime(0, 0, 0, $month, 1)),
                    'reason' => 'Historically low leave usage in your department',
                    'start' => $startDate,
                    'start_date' => $startDate,
                    'end' => $endDate,
                    'end_date' => $endDate,
                ];
            }
            
            // Strategy 2: Check current department occupancy
            $currentRequests = LeaveRequest::join('users', 'leave_requests.employee_id', '=', 'users.id')
                ->where('users.primary_department_id', $employee->primary_department_id)
                ->whereIn('leave_requests.status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval', 'approved_pending_docs', 'on_leave'])
                ->whereMonth('leave_requests.start_date', date('n'))
                ->count();
            
            if ($currentRequests == 0) {
                $startDate = date('Y-m-d', strtotime('+1 week'));
                $endDate = date('Y-m-d', strtotime('+4 weeks'));
                
                $optimalPeriods[] = [
                    'type' => 'current_availability',
                    'period' => 'Next 2-4 weeks',
                    'reason' => 'No current leave requests in your department',
                    'start' => $startDate,
                    'start_date' => $startDate,
                    'end' => $endDate,
                    'end_date' => $endDate,
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Error in getOptimalLeavePeriods: " . $e->getMessage());
        }
        
        return $optimalPeriods;
    }
    
    /**
     * Generate next approval letter number in format: date-001
     */
    private function generateApprovalLetterNumber($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $dateStr = $date->format('Ymd');
        
        // Find the highest number for this date
        $lastLetter = LeaveRequest::whereNotNull('approval_letter_number')
            ->whereDate('approval_date', $date->format('Y-m-d'))
            ->where('approval_letter_number', 'like', "{$dateStr}-%")
            ->orderBy('approval_letter_number', 'desc')
            ->value('approval_letter_number');
        
        if ($lastLetter && preg_match('/\d{8}-(\d{3})/', $lastLetter, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $dateStr . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate next payment voucher number in format: date-001
     */
    private function generatePaymentVoucherNumber($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $dateStr = $date->format('Ymd');
        
        // Find the highest number for this date
        $lastVoucher = LeaveRequest::whereNotNull('payment_voucher_number')
            ->whereDate('payment_date', $date->format('Y-m-d'))
            ->where('payment_voucher_number', 'like', "{$dateStr}-%")
            ->orderBy('payment_voucher_number', 'desc')
            ->value('payment_voucher_number');
        
        if ($lastVoucher && preg_match('/\d{8}-(\d{3})/', $lastVoucher, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $dateStr . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    private function generateLeaveDocuments($leaveRequest, $user)
    {
        try {
            // Generate leave certificate HTML
        $leaveCertificate = $this->generateLeaveCertificate($leaveRequest);
            
            // Store in LeaveDocument table if model exists
            if (class_exists('App\Models\LeaveDocument')) {
        LeaveDocument::create([
            'leave_request_id' => $leaveRequest->id,
            'document_type' => 'leave_certificate',
            'document_content' => $leaveCertificate,
            'generated_by' => $user->id,
            'generated_at' => now(),
        ]);
            }
        
        // Generate fare certificate if applicable
        if ($leaveRequest->fare_approved_amount > 0) {
            $fareCertificate = $this->generateFareCertificate($leaveRequest);
                
                if (class_exists('App\Models\LeaveDocument')) {
            LeaveDocument::create([
                'leave_request_id' => $leaveRequest->id,
                'document_type' => 'fare_certificate',
                'document_content' => $fareCertificate,
                'generated_by' => $user->id,
                'generated_at' => now(),
            ]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('LeaveDocument creation failed: ' . $e->getMessage(), [
                'request_id' => $leaveRequest->id,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - document generation is optional, PDFs are still available via routes
        }
    }
    
    private function generateLeaveCertificate($leaveRequest)
    {
        $startDate = $leaveRequest->start_date->format('F j, Y');
        $endDate = $leaveRequest->end_date->format('F j, Y');
        $approvalDate = $leaveRequest->approval_date ? $leaveRequest->approval_date->format('F j, Y') : now()->format('F j, Y');
        
        return view('modules.hr.documents.leave-certificate', compact('leaveRequest', 'startDate', 'endDate', 'approvalDate'))->render();
    }
    
    private function generateFareCertificate($leaveRequest)
    {
        $amountWords = $this->convertNumberToWords($leaveRequest->fare_approved_amount);
        $paymentDate = $leaveRequest->payment_date ? $leaveRequest->payment_date->format('F j, Y') : now()->format('F j, Y');
        $amountFormatted = number_format($leaveRequest->fare_approved_amount, 2);
        
        return view('modules.hr.documents.fare-certificate', compact('leaveRequest', 'amountWords', 'paymentDate', 'amountFormatted'))->render();
    }
    
    /**
     * Generate PDF for Leave Certificate
     */
    public function generateLeaveCertificatePdf($id)
    {
        try {
            $user = Auth::user();
            
        $leaveRequest = LeaveRequest::with([
                'employee:id,name,email,primary_department_id',
                'employee.primaryDepartment:id,name',
                'employee.employee:id,user_id,position',
                'leaveType:id,name,description',
                'dependents:id,leave_request_id,name,relationship,fare_amount',
                'reviewer:id,name,email',
                'documentProcessor:id,name,email'
        ])->findOrFail($id);
        
        // Check access
        if ($leaveRequest->employee_id !== $user->id && !$user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'System Admin'])) {
                abort(403, 'You do not have permission to generate this document.');
            }
            
            // Ensure all date fields are Carbon instances
            if (!$leaveRequest->start_date instanceof Carbon) {
                $leaveRequest->start_date = Carbon::parse($leaveRequest->start_date);
            }
            if (!$leaveRequest->end_date instanceof Carbon) {
                $leaveRequest->end_date = Carbon::parse($leaveRequest->end_date);
            }
            if ($leaveRequest->approval_date && !$leaveRequest->approval_date instanceof Carbon) {
                $leaveRequest->approval_date = Carbon::parse($leaveRequest->approval_date);
        }
        
        $startDate = $leaveRequest->start_date->format('F j, Y');
        $endDate = $leaveRequest->end_date->format('F j, Y');
        $approvalDate = $leaveRequest->approval_date ? $leaveRequest->approval_date->format('F j, Y') : now()->format('F j, Y');
        
            // Check logo path
            $logoPath = public_path('assets/img/office_link_logo.png');
            if (!file_exists($logoPath)) {
                $alternativePaths = [
                    public_path('images/logo.png'),
                    public_path('img/logo.png'),
                    public_path('assets/images/logo.png'),
                ];
                foreach ($alternativePaths as $altPath) {
                    if (file_exists($altPath)) {
                        $logoPath = $altPath;
                        break;
                    }
                }
                if (!file_exists($logoPath)) {
                    $logoPath = null;
                }
            }
            
            $data = compact('leaveRequest', 'startDate', 'endDate', 'approvalDate', 'logoPath');
            
            try {
        $pdf = Pdf::loadView('modules.hr.documents.leave-certificate', $data);
        $pdf->setPaper('A4', 'portrait');
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('isHtml5ParserEnabled', true);
                $pdf->setOption('isRemoteEnabled', true);
        
                $employeeName = $leaveRequest->employee->name ?? 'Employee';
                $fileName = 'Leave_Certificate_' . preg_replace('/[^A-Za-z0-9_]/', '_', $employeeName) . '_' . $leaveRequest->id . '.pdf';
        
        return $pdf->stream($fileName);
            } catch (\Exception $pdfError) {
                \Log::error('PDF Generation View Error: ' . $pdfError->getMessage(), [
                    'user_id' => Auth::id(),
                    'request_id' => $id,
                    'view' => 'modules.hr.documents.leave-certificate',
                    'trace' => $pdfError->getTraceAsString()
                ]);
                throw $pdfError;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Leave Request Not Found for Certificate PDF: ' . $id, [
                'user_id' => Auth::id(),
                'request_id' => $id
            ]);
            abort(404, 'Leave request not found.');
        } catch (\Exception $e) {
            \Log::error('Leave Certificate PDF Generation Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (app()->environment('local', 'development')) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF Generation Error: ' . $e->getMessage(),
                    'error' => $e->getTraceAsString()
                ], 500);
            } else {
                abort(500, 'An error occurred while generating the leave certificate. Please contact support.');
            }
        }
    }
    
    /**
     * Generate PDF for Fare Certificate
     */
    public function generateFareCertificatePdf($id)
    {
        try {
            $user = Auth::user();
            
        $leaveRequest = LeaveRequest::with([
                'employee:id,name,email,primary_department_id',
                'employee.primaryDepartment:id,name',
                'employee.employee:id,user_id,position',
                'leaveType:id,name,description',
                'dependents:id,leave_request_id,name,relationship,fare_amount',
                'reviewer:id,name,email',
                'documentProcessor:id,name,email'
        ])->findOrFail($id);
        
        // Check access
        if ($leaveRequest->employee_id !== $user->id && !$user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'System Admin'])) {
                abort(403, 'You do not have permission to generate this document.');
        }
        
        if ($leaveRequest->fare_approved_amount <= 0) {
            abort(404, 'Fare certificate not available for this leave request.');
        }
        
        $amountWords = $this->convertNumberToWords($leaveRequest->fare_approved_amount);
        $paymentDate = $leaveRequest->payment_date ? $leaveRequest->payment_date->format('F j, Y') : now()->format('F j, Y');
        $amountFormatted = number_format($leaveRequest->fare_approved_amount, 2);
        
        $data = compact('leaveRequest', 'amountWords', 'paymentDate', 'amountFormatted');
        
        $pdf = Pdf::loadView('modules.hr.documents.fare-certificate', $data);
        $pdf->setPaper('A4', 'portrait');
        
            $fileName = 'Fare_Certificate_' . str_replace(' ', '_', $leaveRequest->employee->name ?? 'Employee') . '_' . $leaveRequest->id . '.pdf';
        
        return $pdf->stream($fileName);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Leave request not found.');
        } catch (\Exception $e) {
            \Log::error('Fare Certificate PDF Generation Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'An error occurred while generating the fare certificate.');
        }
    }
    
    /**
     * Generate PDF for Leave Approval Letter
     */
    public function generateApprovalLetterPdf($id)
    {
        try {
            $user = Auth::user();
            
            $leaveRequest = LeaveRequest::with([
                'employee:id,name,email,primary_department_id',
                'employee.primaryDepartment:id,name',
                'employee.employee:id,user_id,position',
                'leaveType:id,name,description',
                'dependents:id,leave_request_id,name,relationship,fare_amount',
                'reviewer:id,name,email',
                'documentProcessor:id,name,email'
            ])->findOrFail($id);
            
            // Check access
            if ($leaveRequest->employee_id !== $user->id && !$user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'System Admin'])) {
                abort(403, 'You do not have permission to generate this document.');
            }
            
            // Ensure all date fields are Carbon instances
            if (!$leaveRequest->start_date instanceof Carbon) {
                $leaveRequest->start_date = Carbon::parse($leaveRequest->start_date);
            }
            if (!$leaveRequest->end_date instanceof Carbon) {
                $leaveRequest->end_date = Carbon::parse($leaveRequest->end_date);
            }
            if ($leaveRequest->approval_date && !$leaveRequest->approval_date instanceof Carbon) {
                $leaveRequest->approval_date = Carbon::parse($leaveRequest->approval_date);
            }
            
            $data = compact('leaveRequest');
            
            try {
                $pdf = Pdf::loadView('modules.hr.documents.approval-letter', $data);
                $pdf->setPaper('A4', 'portrait');
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('isHtml5ParserEnabled', true);
                $pdf->setOption('isRemoteEnabled', true);
                
                $employeeName = $leaveRequest->employee->name ?? 'Employee';
                $fileName = 'Approval_Letter_' . preg_replace('/[^A-Za-z0-9_]/', '_', $employeeName) . '_' . $leaveRequest->id . '.pdf';
                
                return $pdf->stream($fileName);
            } catch (\Exception $pdfError) {
                \Log::error('Approval Letter PDF Generation Error: ' . $pdfError->getMessage(), [
                    'user_id' => Auth::id(),
                    'request_id' => $id,
                    'view' => 'modules.hr.documents.approval-letter',
                    'trace' => $pdfError->getTraceAsString()
                ]);
                throw $pdfError;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Leave request not found.');
        } catch (\Exception $e) {
            \Log::error('Approval Letter PDF Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'An error occurred while generating the approval letter.');
        }
    }
    
    /**
     * Generate PDF for complete leave request summary
     */
    public function generateLeaveSummaryPdf($id)
    {
        try {
            $user = Auth::user();
            
            // Load leave request with all necessary relationships
        $leaveRequest = LeaveRequest::with([
                'employee:id,name,email,primary_department_id',
                'employee.primaryDepartment:id,name',
                'employee.employee:id,user_id,position',
                'leaveType:id,name,description,max_days_per_year',
                'dependents:id,leave_request_id,name,relationship,fare_amount,certificate_path',
                'reviewer:id,name,email',
                'documentProcessor:id,name,email'
        ])->findOrFail($id);
        
        // Check access
        if ($leaveRequest->employee_id !== $user->id && !$user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'System Admin'])) {
                abort(403, 'You do not have permission to generate this document.');
            }
            
            // Ensure all date fields are Carbon instances
            if (!$leaveRequest->start_date instanceof Carbon) {
                $leaveRequest->start_date = Carbon::parse($leaveRequest->start_date);
            }
            if (!$leaveRequest->end_date instanceof Carbon) {
                $leaveRequest->end_date = Carbon::parse($leaveRequest->end_date);
            }
            if ($leaveRequest->approval_date && !$leaveRequest->approval_date instanceof Carbon) {
                $leaveRequest->approval_date = Carbon::parse($leaveRequest->approval_date);
            }
            if ($leaveRequest->payment_date && !$leaveRequest->payment_date instanceof Carbon) {
                $leaveRequest->payment_date = Carbon::parse($leaveRequest->payment_date);
            }
            if ($leaveRequest->actual_return_date && !$leaveRequest->actual_return_date instanceof Carbon) {
                $leaveRequest->actual_return_date = Carbon::parse($leaveRequest->actual_return_date);
            }
            
            // Check logo path - use default if not exists
            $logoPath = public_path('assets/img/office_link_logo.png');
            if (!file_exists($logoPath)) {
                // Try alternative paths
                $alternativePaths = [
                    public_path('images/logo.png'),
                    public_path('img/logo.png'),
                    public_path('assets/images/logo.png'),
                ];
                foreach ($alternativePaths as $altPath) {
                    if (file_exists($altPath)) {
                        $logoPath = $altPath;
                        break;
                    }
                }
                // If still not found, set to null
                if (!file_exists($logoPath)) {
                    $logoPath = null;
                }
            }
            
            // Prepare data with safe defaults
        $data = [
            'leaveRequest' => $leaveRequest,
                'logoPath' => $logoPath,
        ];
        
            // Generate PDF with error handling
            try {
        $pdf = Pdf::loadView('modules.hr.documents.leave-summary', $data);
        $pdf->setPaper('A4', 'portrait');
        
                // Set PDF options for better compatibility
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('isHtml5ParserEnabled', true);
                $pdf->setOption('isRemoteEnabled', true);
                
                $employeeName = $leaveRequest->employee->name ?? 'Employee';
                $fileName = 'Leave_Summary_' . preg_replace('/[^A-Za-z0-9_]/', '_', $employeeName) . '_' . $leaveRequest->id . '.pdf';
        
        return $pdf->stream($fileName);
            } catch (\Exception $pdfError) {
                \Log::error('PDF Generation View Error: ' . $pdfError->getMessage(), [
                    'user_id' => Auth::id(),
                    'request_id' => $id,
                    'view' => 'modules.hr.documents.leave-summary',
                    'trace' => $pdfError->getTraceAsString()
                ]);
                throw $pdfError;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Leave Request Not Found for PDF: ' . $id, [
                'user_id' => Auth::id(),
                'request_id' => $id
            ]);
            abort(404, 'Leave request not found.');
        } catch (\Exception $e) {
            \Log::error('Leave Summary PDF Generation Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a user-friendly error response
            if (app()->environment('local', 'development')) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF Generation Error: ' . $e->getMessage(),
                    'error' => $e->getTraceAsString()
                ], 500);
            } else {
                abort(500, 'An error occurred while generating the leave summary. Please contact support.');
            }
        }
    }
    
    /**
     * Preview Leave Certificate (HTML)
     */
    public function previewLeaveCertificate($id)
    {
        try {
            $user = Auth::user();
            
            $leaveRequest = LeaveRequest::with([
                'employee:id,name,email,primary_department_id',
                'employee.primaryDepartment:id,name',
                'employee.employee:id,user_id,position',
                'leaveType:id,name,description',
                'dependents:id,leave_request_id,name,relationship,fare_amount',
                'reviewer:id,name,email',
                'documentProcessor:id,name,email'
            ])->findOrFail($id);
            
            // Check access
            if ($leaveRequest->employee_id !== $user->id && !$user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'System Admin'])) {
                abort(403, 'You do not have permission to view this document.');
            }
            
            // Ensure all date fields are Carbon instances
            if (!$leaveRequest->start_date instanceof Carbon) {
                $leaveRequest->start_date = Carbon::parse($leaveRequest->start_date);
            }
            if (!$leaveRequest->end_date instanceof Carbon) {
                $leaveRequest->end_date = Carbon::parse($leaveRequest->end_date);
            }
            if ($leaveRequest->approval_date && !$leaveRequest->approval_date instanceof Carbon) {
                $leaveRequest->approval_date = Carbon::parse($leaveRequest->approval_date);
            }
            
            $startDate = $leaveRequest->start_date->format('F j, Y');
            $endDate = $leaveRequest->end_date->format('F j, Y');
            $approvalDate = $leaveRequest->approval_date ? $leaveRequest->approval_date->format('F j, Y') : now()->format('F j, Y');
            
            return view('modules.hr.documents.leave-certificate', compact('leaveRequest', 'startDate', 'endDate', 'approvalDate'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Leave request not found.');
        } catch (\Exception $e) {
            \Log::error('Leave Certificate Preview Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if (app()->environment('local', 'development')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preview Error: ' . $e->getMessage()
                ], 500);
            } else {
                abort(500, 'An error occurred while loading the preview. Please contact support.');
            }
        }
    }
    
    /**
     * Preview Fare Certificate (HTML)
     */
    public function previewFareCertificate($id)
    {
        try {
            $user = Auth::user();
            
            $leaveRequest = LeaveRequest::with([
                'employee:id,name,email,primary_department_id',
                'employee.primaryDepartment:id,name',
                'employee.employee:id,user_id,position',
                'leaveType:id,name,description',
                'dependents:id,leave_request_id,name,relationship,fare_amount',
                'reviewer:id,name,email',
                'documentProcessor:id,name,email'
            ])->findOrFail($id);
            
            // Check access
            if ($leaveRequest->employee_id !== $user->id && !$user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'System Admin'])) {
                abort(403, 'You do not have permission to view this document.');
            }
            
            // Calculate total fare from dependents if fare_approved_amount is not set
            $fareAmount = $leaveRequest->fare_approved_amount;
            if ($fareAmount <= 0 && $leaveRequest->dependents && $leaveRequest->dependents->count() > 0) {
                $fareAmount = $leaveRequest->dependents->sum('fare_amount');
            }
            
            // Allow preview even if fare is 0, but show a message
            if ($fareAmount <= 0) {
                $amountWords = 'Zero';
                $amountFormatted = '0.00';
            } else {
                $amountWords = $this->convertNumberToWords($fareAmount);
                $amountFormatted = number_format($fareAmount, 2);
            }
            
            $paymentDate = $leaveRequest->payment_date ? $leaveRequest->payment_date->format('F j, Y') : now()->format('F j, Y');
            
            return view('modules.hr.documents.fare-certificate', compact('leaveRequest', 'amountWords', 'paymentDate', 'amountFormatted'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Leave request not found.');
        } catch (\Exception $e) {
            \Log::error('Fare Certificate Preview Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if (app()->environment('local', 'development')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preview Error: ' . $e->getMessage()
                ], 500);
            } else {
                abort(500, 'An error occurred while loading the preview. Please contact support.');
            }
        }
    }
    
    /**
     * Preview Combined Certificate (Leave + Fare in single document)
     */
    public function previewCombinedCertificate($id)
    {
        try {
            $user = Auth::user();
            
            $leaveRequest = LeaveRequest::with([
                'employee:id,name,email,primary_department_id',
                'employee.primaryDepartment:id,name',
                'employee.employee:id,user_id,position',
                'leaveType:id,name,description',
                'dependents:id,leave_request_id,name,relationship,fare_amount',
                'reviewer:id,name,email',
                'documentProcessor:id,name,email'
            ])->findOrFail($id);
            
            // Check access
            if ($leaveRequest->employee_id !== $user->id && !$user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'System Admin'])) {
                abort(403, 'You do not have permission to view this document.');
            }
            
            // Ensure all date fields are Carbon instances
            if (!$leaveRequest->start_date instanceof Carbon) {
                $leaveRequest->start_date = Carbon::parse($leaveRequest->start_date);
            }
            if (!$leaveRequest->end_date instanceof Carbon) {
                $leaveRequest->end_date = Carbon::parse($leaveRequest->end_date);
            }
            if ($leaveRequest->approval_date && !$leaveRequest->approval_date instanceof Carbon) {
                $leaveRequest->approval_date = Carbon::parse($leaveRequest->approval_date);
            }
            
            $startDate = $leaveRequest->start_date->format('F j, Y');
            $endDate = $leaveRequest->end_date->format('F j, Y');
            $approvalDate = $leaveRequest->approval_date ? $leaveRequest->approval_date->format('F j, Y') : now()->format('F j, Y');
            
            $amountWords = null;
            $paymentDate = null;
            $amountFormatted = null;
            
            if ($leaveRequest->fare_approved_amount > 0) {
                $amountWords = $this->convertNumberToWords($leaveRequest->fare_approved_amount);
                $paymentDate = $leaveRequest->payment_date ? $leaveRequest->payment_date->format('F j, Y') : now()->format('F j, Y');
                $amountFormatted = number_format($leaveRequest->fare_approved_amount, 2);
            }
            
            return view('modules.hr.documents.combined-certificate', compact(
                'leaveRequest', 
                'startDate', 
                'endDate', 
                'approvalDate',
                'amountWords',
                'paymentDate',
                'amountFormatted'
            ));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Leave request not found.');
        } catch (\Exception $e) {
            \Log::error('Combined Certificate Preview Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if (app()->environment('local', 'development')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preview Error: ' . $e->getMessage()
                ], 500);
            } else {
                abort(500, 'An error occurred while loading the preview. Please contact support.');
            }
        }
    }
    
    /**
     * Create petty cash request automatically when fare is approved
     */
    private function createPettyCashRequest($leaveRequest, $fareAmount, $paymentVoucherNumber)
    {
        try {
            $dateStr = date('Ymd');
            $lastVoucher = PettyCashVoucher::whereDate('created_at', today())
                ->where('voucher_no', 'like', 'PCV' . $dateStr . '-%')
                ->orderBy('voucher_no', 'desc')
                ->first();
            
            $sequence = 1;
            if ($lastVoucher && preg_match('/PCV\d{8}-(\d{3})/', $lastVoucher->voucher_no, $matches)) {
                $sequence = intval($matches[1]) + 1;
            }
            
            $voucherNo = 'PCV' . $dateStr . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
            // Create petty cash voucher
            $voucher = PettyCashVoucher::create([
                'voucher_no' => $voucherNo,
                'date' => $leaveRequest->payment_date ?? now(),
                'payee' => $leaveRequest->employee->name,
                'purpose' => "Leave fare allowance for {$leaveRequest->leaveType->name} - Payment Voucher: {$paymentVoucherNumber}",
                'amount' => $fareAmount,
                'created_by' => $leaveRequest->employee_id,
                'status' => 'pending_accountant',
            ]);
            
            // Create voucher line
            PettyCashVoucherLine::create([
                'voucher_id' => $voucher->id,
                'description' => "Fare allowance for leave period: {$leaveRequest->start_date->format('M d')} - {$leaveRequest->end_date->format('M d, Y')}",
                'qty' => 1,
                'unit_price' => $fareAmount,
                'total' => $fareAmount,
            ]);
            
            // Notify accountant
            try {
                $this->notificationService->notifyAccountant(
                    "Auto-generated petty cash request #{$voucherNo} for leave fare allowance of TZS " . number_format($fareAmount, 2) . " for {$leaveRequest->employee->name} is pending your review.",
                    route('petty-cash.accountant.index'),
                    'Auto-Generated Petty Cash Request - Leave Fare',
                    ['voucher_no' => $voucherNo, 'employee_name' => $leaveRequest->employee->name, 'amount' => number_format($fareAmount, 2)]
                );
            } catch (\Exception $notifError) {
                \Log::warning('Failed to notify accountant about petty cash request: ' . $notifError->getMessage());
            }
            
            \Log::info('Petty cash request created automatically for leave fare', [
                'leave_request_id' => $leaveRequest->id,
                'petty_cash_voucher_id' => $voucher->id,
                'voucher_no' => $voucherNo,
                'amount' => $fareAmount
            ]);
            
            return $voucher;
        } catch (\Exception $e) {
            \Log::error('Error creating petty cash request for leave fare: ' . $e->getMessage(), [
                'leave_request_id' => $leaveRequest->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    private function convertNumberToWords($number)
    {
        $ones = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine"];
        $tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
        $teens = ["Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
        
        $words = "";
        $number = (int)$number;
        
        if ($number == 0) {
            return "Zero";
        }
        
        if ($number >= 1000000) {
            $words .= $this->convertNumberToWords(floor($number / 1000000)) . " Million ";
            $number %= 1000000;
        }
        
        if ($number >= 1000) {
            $words .= $this->convertNumberToWords(floor($number / 1000)) . " Thousand ";
            $number %= 1000;
        }
        
        if ($number >= 100) {
            $words .= $ones[floor($number / 100)] . " Hundred ";
            $number %= 100;
        }
        
        if ($number >= 20) {
            $words .= $tens[floor($number / 10)] . " ";
            $number %= 10;
        }
        
        if ($number >= 10 && $number < 20) {
            $words .= $teens[$number - 10] . " ";
            $number = 0;
        }
        
        if ($number > 0) {
            $words .= $ones[$number] . " ";
        }
        
        return trim($words) . " Tanzanian Shillings Only";
    }
    
    /**
     * Handle bulk operations for leave requests
     */
    public function bulkOperations(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }
        
        $action = $request->input('action');
        $requestIds = $request->input('request_ids', []);
        
        try {
            switch ($action) {
                case 'approve':
                    return $this->bulkApprove($requestIds, $user);
                    
                case 'reject':
                    return $this->bulkReject($requestIds, $user, $request->input('reason', 'Bulk rejection'));
                    
                case 'process_documents':
                    return $this->bulkProcessDocuments($requestIds, $user);
                    
                case 'cancel':
                    return $this->bulkCancel($requestIds);
                    
                case 'bulk_update_balance':
                    return $this->bulkUpdateBalance($request->input('balances', []), $request->input('total_days_allotted', 28), $request->input('carry_forward_days', 0));
                    
                case 'bulk_reset_balance':
                    return $this->bulkResetBalance($request->input('balances', []));
                    
                case 'create_from_recommendations':
                    return $this->bulkCreateFromRecommendations($request->input('recommendation_ids', []));
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action.'
                    ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Bulk Operations Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function bulkApprove($requestIds, $user)
    {
        $requests = LeaveRequest::whereIn('id', $requestIds)
            ->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval'])
            ->get();
        
        $approved = 0;
        foreach ($requests as $req) {
            if ($req->status === 'pending_hr_review' && ($user->hasRole('HR Officer') || $user->hasRole('System Admin'))) {
                $req->update([
                    'status' => 'pending_hod_approval',
                    'reviewed_by' => $user->id,
                    'hr_reviewed_at' => now()
                ]);
                $approved++;
            } elseif ($req->status === 'pending_hod_approval' && ($user->hasRole('HOD') || $user->hasRole('System Admin'))) {
                $req->update([
                    'status' => 'pending_ceo_approval',
                    'hod_reviewed_at' => now()
                ]);
                $approved++;
            } elseif ($req->status === 'pending_ceo_approval' && ($user->hasRole('CEO') || $user->hasRole('System Admin'))) {
                $req->update([
                    'status' => 'approved_pending_docs',
                    'ceo_reviewed_at' => now()
                ]);
                $approved++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Successfully approved {$approved} request(s)."
        ]);
    }
    
    private function bulkReject($requestIds, $user, $reason)
    {
        $requests = LeaveRequest::whereIn('id', $requestIds)
            ->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval'])
            ->get();
        
        $rejected = 0;
        foreach ($requests as $req) {
            $req->update([
                'status' => 'rejected',
                'reviewed_by' => $user->id,
                'rejection_reason' => $reason,
                'rejected_at' => now()
            ]);
            $rejected++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Successfully rejected {$rejected} request(s)."
        ]);
    }
    
    private function bulkProcessDocuments($requestIds, $user)
    {
        $requests = LeaveRequest::whereIn('id', $requestIds)
            ->where('status', 'approved_pending_docs')
            ->get();
        
        $processed = 0;
        foreach ($requests as $req) {
            $req->update([
                'status' => 'on_leave',
                'documents_processed_by' => $user->id,
                'documents_processed_at' => now()
            ]);
            $processed++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Successfully processed documents for {$processed} request(s)."
        ]);
    }
    
    private function bulkCancel($requestIds)
    {
        $requests = LeaveRequest::whereIn('id', $requestIds)
            ->whereNotIn('status', ['completed', 'on_leave'])
            ->get();
        
        $cancelled = 0;
        foreach ($requests as $req) {
            $req->update(['status' => 'cancelled']);
            $cancelled++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Successfully cancelled {$cancelled} request(s)."
        ]);
    }
    
    private function bulkUpdateBalance($balances, $totalDays, $carryForward)
    {
        $annualType = LeaveType::where('name', 'like', '%annual%')->first();
        if (!$annualType) {
            return response()->json([
                'success' => false,
                'message' => 'Annual leave type not found.'
            ], 404);
        }
        
        $updated = 0;
        foreach ($balances as $balance) {
            LeaveBalance::updateOrCreate(
                [
                    'employee_id' => $balance['employee_id'],
                    'leave_type_id' => $annualType->id,
                    'financial_year' => $balance['year'],
                ],
                [
                    'total_days_allotted' => $totalDays,
                    'carry_forward_days' => $carryForward,
                ]
            );
            $updated++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$updated} balance(s)."
        ]);
    }
    
    private function bulkResetBalance($balances)
    {
        $annualType = LeaveType::where('name', 'like', '%annual%')->first();
        if (!$annualType) {
            return response()->json([
                'success' => false,
                'message' => 'Annual leave type not found.'
            ], 404);
        }
        
        $reset = 0;
        foreach ($balances as $balance) {
            $leaveBalance = LeaveBalance::where('employee_id', $balance['employee_id'])
                ->where('leave_type_id', $annualType->id)
                ->where('financial_year', $balance['year'])
                ->first();
            
            if ($leaveBalance) {
                $leaveBalance->update(['days_taken' => 0]);
                $reset++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Successfully reset {$reset} balance(s)."
        ]);
    }
    
    private function bulkCreateFromRecommendations($recommendationIds)
    {
        $recommendations = \App\Models\LeaveRecommendation::whereIn('id', $recommendationIds)->get();
        
        $annualType = LeaveType::where('name', 'like', '%annual%')->first();
        if (!$annualType) {
            return response()->json([
                'success' => false,
                'message' => 'Annual leave type not found.'
            ], 404);
        }
        
        $created = 0;
        $errors = [];
        
        foreach ($recommendations as $rec) {
            try {
                // Check if employee already has an active request
                $activeRequest = LeaveRequest::where('employee_id', $rec->employee_id)
                    ->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval', 'approved_pending_docs', 'on_leave'])
                    ->first();
                
                if ($activeRequest) {
                    $errors[] = "Employee {$rec->employee->name} already has an active leave request.";
                    continue;
                }
                
                // Create leave request from recommendation
                LeaveRequest::create([
                    'employee_id' => $rec->employee_id,
                    'leave_type_id' => $annualType->id,
                    'start_date' => $rec->recommended_start_date,
                    'end_date' => $rec->recommended_end_date,
                    'total_days' => \Carbon\Carbon::parse($rec->recommended_start_date)->diffInDays(\Carbon\Carbon::parse($rec->recommended_end_date)) + 1,
                    'reason' => 'Auto-generated from recommendation',
                    'leave_location' => 'TBD',
                    'status' => 'pending_hr_review'
                ]);
                
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Error creating request for recommendation ID {$rec->id}: " . $e->getMessage();
            }
        }
        
        $message = "Successfully created {$created} leave request(s).";
        if (count($errors) > 0) {
            $message .= " Errors: " . implode('; ', $errors);
        }
        
        return response()->json([
            'success' => $created > 0,
            'message' => $message
        ]);
    }
    
    /**
     * Export bulk operations
     */
    public function bulkOperationsExport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Authorization Failed.');
        }
        
        $action = $request->input('action');
        $ids = explode(',', $request->input('ids', ''));
        
        // Export functionality would go here
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}