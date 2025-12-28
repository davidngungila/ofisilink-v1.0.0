<?php

namespace App\Http\Controllers;

use App\Models\RackCategory;
use App\Models\RackFolder;
use App\Models\RackFile;
use App\Models\RackFileRequest;
use App\Models\RackActivity;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhysicalRackController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index()
    {
        // Redirect to dashboard
        return redirect()->route('modules.files.physical.dashboard');
    }
    
    /**
     * Dashboard Page
     */
    public function dashboard()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        // Determine permissions
        $canManageFiles = in_array('System Admin', $userRoles) || 
                         in_array('HR Officer', $userRoles) || 
                         in_array('HOD', $userRoles) || 
                         in_array('CEO', $userRoles) ||
                         in_array('Record Officer', $userRoles);
        
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        $isStaff = count($userRoles) === 1 && in_array('Staff', $userRoles);
        $currentDeptId = $user->department_id ?? null;
        
        // Get rack categories
        $rackCategories = RackCategory::where('status', 'active')->orderBy('name')->get();
        
        // Get root rack folders (no parent structure for physical racks)
        $rootFoldersQuery = RackFolder::with(['category', 'department', 'creator'])
            ->withCount(['files' => function($query) {
                $query->where('status', '!=', 'archived');
            }])
            ->where('status', 'active');
        
        if (!$canViewAll) {
            $rootFoldersQuery->where(function($query) use ($currentDeptId) {
                $query->where('access_level', 'public')
                    ->orWhere(function($q) use ($currentDeptId) {
                        $q->where('access_level', 'department')
                          ->where('department_id', $currentDeptId);
                    });
            });
        }
        
        $rootFolders = $rootFoldersQuery->orderBy('name')->get();
        
        // Get dashboard stats
        $stats = $this->getDashboardStats($user, $canViewAll, $currentDeptId);
        
        // Get recent activities
        $recentActivity = RackActivity::with(['user', 'folder'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('modules.files.physical.dashboard', compact(
            'rootFolders',
            'rackCategories',
            'stats',
            'recentActivity',
            'canManageFiles',
            'canViewAll',
            'isStaff',
            'user'
        ));
    }
    
    /**
     * Get Dashboard Statistics
     */
    private function getDashboardStats($user, $canViewAll, $currentDeptId)
    {
        $totalFolders = RackFolder::count();
        $totalFiles = RackFile::count();
        
        $userQuery = RackFile::query();
        if (!$canViewAll) {
            $userQuery->whereHas('folder', function($q) use ($user, $currentDeptId) {
                $q->where('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  });
            });
        }
        
        $issuedFiles = $userQuery->where('status', 'issued')->count();
        $pendingRequests = RackFileRequest::where('status', 'pending')->count();
        $myPendingRequests = RackFileRequest::where('requested_by', $user->id)
            ->where('status', 'pending')->count();
        
        return [
            'total_folders' => $totalFolders,
            'total_files' => $totalFiles,
            'issued_files' => $issuedFiles,
            'pending_requests' => $pendingRequests,
            'my_pending_requests' => $myPendingRequests
        ];
    }
    
    /**
     * Rack Folder Detail Page
     */
    public function rackDetail($rackId)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canManageFiles = in_array('System Admin', $userRoles) || 
                         in_array('HR Officer', $userRoles) || 
                         in_array('HOD', $userRoles) || 
                         in_array('CEO', $userRoles) ||
                         in_array('Record Officer', $userRoles);
        
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        $currentDeptId = $user->department_id ?? null;
        
        // Get rack folder with relationships
        $rack = RackFolder::with(['category', 'department', 'creator'])
            ->withCount(['files'])
            ->findOrFail($rackId);
        
        // Check access
        if (!$canViewAll) {
            if ($rack->access_level === 'private') {
                abort(403, 'You do not have access to this rack folder.');
            } elseif ($rack->access_level === 'department' && $rack->department_id !== $currentDeptId) {
                abort(403, 'You do not have access to this rack folder.');
            }
        }
        
        // Get files in this rack
        // For staff: only show files from public racks or department racks they have access to
        $filesQuery = RackFile::with(['creator', 'holder'])
            ->where('folder_id', $rackId);
        
        // Staff can only see files if the rack is public or their department
        // Admins/HOD/HR can see all files
        if (!$canViewAll) {
            // Staff can see files from public racks or their department racks
            // This is already filtered at rack level, so all files in accessible racks are visible
        }
        
        $files = $filesQuery->orderBy('created_at', 'desc')->paginate(20);
        
        // Get activities
        $activities = RackActivity::with(['user', 'folder'])
            ->where('folder_id', $rackId)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
        
        // Get departments
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.files.physical.rack-detail', compact(
            'rack',
            'files',
            'activities',
            'canManageFiles', 
            'canViewAll',
            'departments',
            'user'
        ));
    }
    
    /**
     * Upload Files Page
     */
    public function upload()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canManageFiles = in_array('System Admin', $userRoles) || 
                         in_array('HR Officer', $userRoles) || 
                         in_array('HOD', $userRoles) || 
                         in_array('CEO', $userRoles) ||
                         in_array('Record Officer', $userRoles);
        
        if (!$canManageFiles) {
            abort(403, 'You do not have permission to upload files.');
        }
        
        $rackFolders = RackFolder::with(['category', 'department'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        $categories = RackCategory::where('status', 'active')->orderBy('name')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.files.physical.upload', compact('rackFolders', 'categories', 'departments', 'user'));
    }
    
    /**
     * Manage Racks & Files Page
     */
    public function manage()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canManageFiles = in_array('System Admin', $userRoles) || 
                         in_array('HR Officer', $userRoles) || 
                         in_array('HOD', $userRoles) || 
                         in_array('CEO', $userRoles) ||
                         in_array('Record Officer', $userRoles);
        
        if (!$canManageFiles) {
            abort(403, 'You do not have permission to manage racks.');
        }
        
        $rackFolders = RackFolder::with(['category', 'department', 'creator'])
            ->withCount(['files'])
            ->orderBy('name')
            ->get();
        
        $files = RackFile::with(['folder', 'creator', 'holder'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        $categories = RackCategory::where('status', 'active')->orderBy('name')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.files.physical.manage', compact('rackFolders', 'files', 'categories', 'departments', 'user'));
    }
    
    /**
     * Search Page
     */
    public function search()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        $currentDeptId = $user->department_id ?? null;
        
        $rackFolders = RackFolder::with(['category', 'department'])
            ->orderBy('name')
            ->get();
        
        $categories = RackCategory::where('status', 'active')->orderBy('name')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.files.physical.search', compact('rackFolders', 'categories', 'departments', 'canViewAll', 'currentDeptId', 'user'));
    }
    
    /**
     * Analytics Page
     */
    public function analytics()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        $currentDeptId = $user->department_id ?? null;
        
        $stats = $this->getDashboardStats($user, $canViewAll, $currentDeptId);
        
        // Get file type distribution
        $fileTypes = RackFile::select(
                DB::raw("CASE 
                    WHEN file_type LIKE '%pdf%' OR file_type LIKE '%PDF%' THEN 'pdf'
                    WHEN file_type LIKE '%image%' OR file_type LIKE '%photo%' THEN 'image'
                    WHEN file_type LIKE '%document%' OR file_type LIKE '%doc%' THEN 'document'
                    WHEN file_type LIKE '%spreadsheet%' OR file_type LIKE '%excel%' THEN 'spreadsheet'
                    ELSE 'other'
                END as file_type"),
                DB::raw('count(*) as count')
            )
            ->groupBy('file_type')
            ->orderBy('count', 'desc')
            ->get();
        
        // Get status distribution
        $statusStats = RackFile::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        // Get activity over time
        $activityData = RackActivity::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('modules.files.physical.analytics', compact(
            'stats',
            'fileTypes',
            'statusStats',
            'activityData',
            'canViewAll',
            'user'
        ));
    }
    
    /**
     * Access Requests Page
     */
    public function accessRequests()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canManageFiles = in_array('System Admin', $userRoles) || 
                         in_array('HR Officer', $userRoles) || 
                         in_array('HOD', $userRoles) || 
                         in_array('CEO', $userRoles) ||
                         in_array('Record Officer', $userRoles);
        
        // Get pending requests
        // Get both pending file requests and return requests
        $pendingRequests = RackFileRequest::with(['requester', 'file.folder'])
            ->whereIn('status', ['pending', 'return_pending'])
            ->orderByRaw("CASE 
                WHEN status = 'return_pending' THEN 1 
                WHEN status = 'pending' THEN 2 
                ELSE 3 
            END")
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get user's own requests
        $myRequests = RackFileRequest::with(['requester', 'file.folder'])
            ->where('requested_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('modules.files.physical.access-requests', compact('pendingRequests', 'myRequests', 'canManageFiles', 'user'));
    }
    
    /**
     * Activity Log Page
     */
    public function activityLog()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        $activities = RackActivity::with(['user', 'folder'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('modules.files.physical.activity-log', compact('activities', 'canViewAll', 'user'));
    }
    
    /**
     * Settings Page
     */
    public function settings()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canManageFiles = in_array('System Admin', $userRoles) || 
                         in_array('HR Officer', $userRoles) || 
                         in_array('HOD', $userRoles) || 
                         in_array('CEO', $userRoles) ||
                         in_array('Record Officer', $userRoles);
        
        if (!$canManageFiles) {
            abort(403, 'You do not have permission to access settings.');
        }
        
        $categories = RackCategory::orderBy('name')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        
        // Load current settings
        $settings = [
            'default_retention' => \App\Models\SystemSetting::getValue('physical_files_default_retention', 5),
            'auto_archive_days' => \App\Models\SystemSetting::getValue('physical_files_auto_archive_days', 365),
            'enable_logging' => \App\Models\SystemSetting::getValue('physical_files_enable_logging', true),
            'default_access_level' => \App\Models\SystemSetting::getValue('physical_files_default_access_level', 'public')
        ];
        
        return view('modules.files.physical.settings', compact('categories', 'departments', 'user', 'settings'));
    }
    
    /**
     * Assign Files/Folders Page
     */
    public function assign()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $canManageFiles = in_array('System Admin', $userRoles) || 
                         in_array('HR Officer', $userRoles) || 
                         in_array('HOD', $userRoles) || 
                         in_array('CEO', $userRoles) ||
                         in_array('Record Officer', $userRoles);
        
        if (!$canManageFiles) {
            abort(403, 'You do not have permission to assign files/folders.');
        }
        
        $rackFolders = RackFolder::with(['category', 'department'])
            ->orderBy('name')
            ->get();
        
        $files = RackFile::with(['folder', 'creator'])
            ->orderBy('file_name')
            ->get();
        
        $users = User::with('roles', 'department')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.files.physical.assign', compact(
            'rackFolders', 
            'files',
            'users',
            'departments',
            'user'
        ));
    }
    
    // AJAX Handlers
    public function handleRequest(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Invalid request']);
        }
        
        $action = $request->input('action');
        $response = ['success' => false, 'message' => 'An unknown error occurred.'];
        
        try {
            DB::beginTransaction();
            
            switch ($action) {
                case 'get_pending_rack_requests':
                    $response = $this->getPendingRackRequests();
                    break;
                case 'process_rack_request':
                    $response = $this->processRackRequest($request);
                    break;
                case 'create_rack_folder':
                    $response = $this->createRackFolder($request);
                    break;
                case 'bulk_create_racks_excel':
                    $response = $this->handleBulkCreateRacksExcel($request);
                    break;
                case 'create_rack_file':
                    $response = $this->createRackFile($request);
                    break;
                case 'create_category':
                    $response = $this->createCategory($request);
                    break;
                case 'update_category':
                    $response = $this->updateCategory($request);
                    break;
                case 'delete_category':
                    $response = $this->deleteCategory($request);
                    break;
                case 'request_physical_file':
                    $response = $this->requestPhysicalFile($request);
                    break;
                case 'assign_physical_file':
                    $response = $this->assignPhysicalFile($request);
                    break;
                case 'return_physical_file':
                    $response = $this->returnPhysicalFile($request);
                    break;
                case 'get_my_rack_requests':
                    $response = $this->getMyRackRequests();
                    break;
                case 'get_rack_folder_details':
                    $response = $this->getRackFolderDetails($request);
                    break;
                case 'get_rack_file_details':
                    $response = $this->getRackFileDetails($request);
                    break;
                case 'update_rack_file':
                    $response = $this->updateRackFile($request);
                    break;
                case 'get_rack_folders':
                    $response = $this->getRackFolders($request);
                    break;
                case 'get_rack_folder_contents':
                    $response = $this->getRackFolderContents($request);
                    break;
                case 'search_rack_files':
                    $response = $this->searchRackFiles($request);
                    break;
                case 'live_search_rack_files':
                    $response = $this->liveSearchRackFiles($request);
                    break;
                case 'get_rack_categories':
                    $response = $this->getRackCategories();
                    break;
                    
                case 'save_settings':
                    $response = $this->saveSettings($request);
                    break;
                    
                case 'update_rack_folder':
                    $response = $this->updateRackFolder($request);
                    break;
                    
                default:
                    $response['message'] = 'Invalid action';
            }
            
            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
        }
        
        return response()->json($response);
    }
    
    private function getPendingRackRequests()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot view pending requests.");
        }
        
        // Get both pending file requests and return requests
        $requests = RackFileRequest::with(['file.folder.category', 'file.holder', 'requester.department', 'approver'])
            ->whereIn('status', ['pending', 'return_pending'])
            ->orderByRaw("CASE 
                WHEN status = 'return_pending' THEN 1 
                WHEN status = 'pending' THEN 2 
                ELSE 3 
            END")
            ->orderByRaw("CASE urgency 
                WHEN 'urgent' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'normal' THEN 3 
                ELSE 4 
            END")
            ->orderBy('created_at', 'asc')
            ->get();
        
        return ['success' => true, 'requests' => $requests];
    }
    
    private function processRackRequest(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot process requests.");
        }
        
        $requestId = $request->input('request_id');
        $decision = $request->input('decision');
        $notes = $request->input('notes', '');
        
        if (!in_array($decision, ['approve', 'reject'])) {
            throw new \Exception("Invalid decision provided.");
        }
        
        $rackRequest = RackFileRequest::findOrFail($requestId);
        
        // Handle return requests (return_pending status)
        if ($rackRequest->status === 'return_pending') {
            if ($decision === 'approve') {
                // Approve return - mark file as available
                $file = $rackRequest->file;
                
                // Check if file is issued (should be, since return is pending)
                if ($file->status !== 'issued') {
                    throw new \Exception("File is not in issued status. Current status: {$file->status}");
                }
                
                // Update request status
                $rackRequest->update([
                    'status' => 'return_approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'manager_notes' => $notes ? ($rackRequest->manager_notes . "\n\nApproval Notes: " . $notes) : $rackRequest->manager_notes
                ]);
                
                // Mark file as available
                $file->update([
                    'status' => 'available',
                    'current_holder' => null,
                    'last_returned' => now()
                ]);
                
                $this->logRackActivity($file->folder_id, 'file_return_approved', $user->id, [
                    'request_id' => $requestId,
                    'file_id' => $rackRequest->file_id,
                    'return_condition' => $rackRequest->manager_notes ?? 'N/A'
                ]);
                
                // Send notifications
                try {
                    $requester = $rackRequest->requester;
                    $fileName = $file->file_name . ' (' . $file->file_number . ')';
                    
                    // Notify requester
                    $this->notificationService->notify(
                        $rackRequest->requested_by,
                        "Your return request for file '{$fileName}' has been approved. The file is now available in the rack.",
                        route('modules.files.physical'),
                        'Return Request Approved'
                    );
                } catch (\Exception $e) {
                    \Log::error('Notification error in processRackRequest (return approve): ' . $e->getMessage());
                }
                
                return ['success' => true, 'message' => 'Return request approved. File is now available.'];
            } else {
                // Reject return - keep file as issued
                $file = $rackRequest->file;
                
                $rackRequest->update([
                    'status' => 'return_rejected',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'manager_notes' => $rackRequest->manager_notes . "\n\nRejection Notes: " . ($notes ?: 'No reason provided')
                ]);
                
                // Revert file status back to issued
                $file->update([
                    'status' => 'issued'
                ]);
                
                $this->logRackActivity($file->folder_id, 'file_return_rejected', $user->id, [
                    'request_id' => $requestId,
                    'file_id' => $rackRequest->file_id,
                    'rejection_reason' => $notes
                ]);
                
                // Send notifications
                try {
                    $requester = $rackRequest->requester;
                    $fileName = $file->file_name . ' (' . $file->file_number . ')';
                    
                    // Notify requester
                    $this->notificationService->notify(
                        $rackRequest->requested_by,
                        "Your return request for file '{$fileName}' has been rejected. " . ($notes ? "Reason: {$notes}" : "Please contact HR for details."),
                        route('modules.files.physical'),
                        'Return Request Rejected'
                    );
                } catch (\Exception $e) {
                    \Log::error('Notification error in processRackRequest (return reject): ' . $e->getMessage());
                }
                
                return ['success' => true, 'message' => 'Return request rejected. File remains issued to the user.'];
            }
        }
        
        // Handle regular file requests (pending status)
        if ($rackRequest->status !== 'pending') {
            throw new \Exception("This request has already been processed.");
        }
        
        if ($decision === 'approve') {
            // Check if file is available before approving
            $file = $rackRequest->file;
            
            // Only allow approval if file status is 'available'
            if ($file->status !== 'available') {
                throw new \Exception("Cannot approve request: File is currently {$file->status}. The file must be returned and available before it can be issued to another person.");
            }
            
            // Check if file has a current holder (should be null for available files)
            // Only prevent if holder is different from requester
            if ($file->current_holder && $file->current_holder !== $rackRequest->requested_by) {
                throw new \Exception("Cannot approve request: File is currently held by another person. The file must be returned first before it can be issued to a new requester.");
            }
            
            // File is available and can be approved - proceed
            
            $rackRequest->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'manager_notes' => $notes
            ]);
            
            $rackRequest->file->update([
                'status' => 'issued',
                'current_holder' => $rackRequest->requested_by
            ]);
            
            $this->logRackActivity($rackRequest->file->folder_id, 'file_request_approved', $user->id, [
                'request_id' => $requestId,
                'file_id' => $rackRequest->file_id
            ]);
            
            // Send notifications
            try {
                $requester = $rackRequest->requester;
                $fileName = $rackRequest->file->file_name . ' (' . $rackRequest->file->file_number . ')';
                
                // Notify requester
                $this->notificationService->notify(
                    $rackRequest->requested_by,
                    "Your file request for '{$fileName}' has been approved. File is now issued to you.",
                    route('modules.files.physical'),
                    'File Request Approved'
                );
                
                // Notify managers (HOD/HR) about the approval
                if ($requester && $requester->primary_department_id) {
                    $this->notificationService->notifyHOD(
                        $requester->primary_department_id,
                        "File request for '{$fileName}' from {$requester->name} has been approved.",
                        route('modules.files.physical'),
                        'File Request Approved'
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Notification error in processRackRequest (approve): ' . $e->getMessage());
            }
            
            return ['success' => true, 'message' => 'Request approved. File status updated to issued.'];
        } else {
            $rackRequest->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'manager_notes' => $notes
            ]);
            
            $this->logRackActivity($rackRequest->file->folder_id, 'file_request_rejected', $user->id, [
                'request_id' => $requestId,
                'file_id' => $rackRequest->file_id
            ]);
            
            // Send notifications
            try {
                $fileName = $rackRequest->file->file_name . ' (' . $rackRequest->file->file_number . ')';
                
                // Notify requester of rejection
                $this->notificationService->notify(
                    $rackRequest->requested_by,
                    "Your file request for '{$fileName}' has been rejected. Please check the notes for details.",
                    route('modules.files.physical'),
                    'File Request Rejected'
                );
            } catch (\Exception $e) {
                \Log::error('Notification error in processRackRequest (reject): ' . $e->getMessage());
            }
            
            return ['success' => true, 'message' => 'Request has been rejected.'];
        }
    }
    
    private function createRackFolder(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot create rack folders.");
        }
        
        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:rack_categories,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'rack_range_start' => 'required|integer|min:1',
            'rack_range_end' => 'required|integer|min:1|gte:rack_range_start',
            'access_level' => 'required|in:public,department,private',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ], [
            'rack_range_end.gte' => 'Rack range end must be greater than or equal to rack range start.'
        ]);
        
        if ($validated['rack_range_start'] > $validated['rack_range_end']) {
            throw new \Exception("Invalid rack range.");
        }
        
        $rackNumber = $this->generateRackNumber($validated['category_id']);
        
        $folder = RackFolder::create([
            'name' => $validated['folder_name'],
            'description' => $validated['description'],
            'rack_number' => $rackNumber,
            'rack_range_start' => $validated['rack_range_start'],
            'rack_range_end' => $validated['rack_range_end'],
            'category_id' => $validated['category_id'],
            'department_id' => $validated['department_id'] ?? null,
            'access_level' => $validated['access_level'],
            'location' => $validated['location'],
            'notes' => $validated['notes'],
            'created_by' => $user->id,
            'status' => 'active'
        ]);
        
        $this->logRackActivity($folder->id, 'created', $user->id, [
            'rack_number' => $rackNumber,
            'category_id' => $validated['category_id'],
            'rack_range' => $validated['rack_range_start'] . '-' . $validated['rack_range_end']
        ]);
        
        // Send SMS notification
        try {
            $this->notificationService->notify(
                $user->id,
                "Rack folder '{$validated['folder_name']}' (Rack: {$rackNumber}) has been created successfully in the physical file management system.",
                route('modules.files.physical'),
                'Rack Folder Created'
            );
        } catch (\Exception $e) {
            \Log::error('SMS notification error in createRackFolder: ' . $e->getMessage());
        }
        
        return ['success' => true, 'message' => 'Rack folder created successfully.', 
                'folder_id' => $folder->id, 'rack_number' => $rackNumber];
    }
    
    private function createRackFile(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot create rack files.");
        }
        
        $validated = $request->validate([
            'folder_id' => 'required|integer|exists:rack_folders,id',
            'file_name' => 'required|string|max:255',
            'file_number' => 'required|string|max:255|unique:rack_files,file_number',
            'description' => 'nullable|string',
            'file_type' => 'required|in:general,contract,financial,legal,hr,technical',
            'confidential_level' => 'required|in:normal,confidential,strictly_confidential',
            'tags' => 'nullable|string|max:255',
            'file_date' => 'nullable|date',
            'retention_period' => 'nullable|integer|min:1',
            'notes' => 'nullable|string'
        ], [
            'file_number.unique' => 'This file number already exists. Please use a different file number.',
            'file_type.required' => 'Please select a file type.',
            'confidential_level.required' => 'Please select a confidentiality level.'
        ]);
        
        $file = RackFile::create([
            'folder_id' => $validated['folder_id'],
            'file_name' => $validated['file_name'],
            'file_number' => $validated['file_number'],
            'description' => $validated['description'],
            'file_type' => $validated['file_type'],
            'confidential_level' => $validated['confidential_level'],
            'tags' => $validated['tags'],
            'file_date' => $validated['file_date'] ?? now(),
            'retention_period' => $validated['retention_period'],
            'notes' => $validated['notes'],
            'created_by' => $user->id,
            'status' => 'available'
        ]);
        
        $this->logRackActivity($validated['folder_id'], 'file_created', $user->id, [
            'file_id' => $file->id,
            'file_number' => $validated['file_number'],
            'file_name' => $validated['file_name']
        ]);
        
        // Send SMS notification
        try {
            $folder = RackFolder::find($validated['folder_id']);
            $folderName = $folder ? $folder->name : 'Unknown';
            $this->notificationService->notify(
                $user->id,
                "Rack file '{$validated['file_name']}' (File #: {$validated['file_number']}) has been created in rack folder '{$folderName}'.",
                route('modules.files.physical'),
                'Rack File Created'
            );
        } catch (\Exception $e) {
            \Log::error('SMS notification error in createRackFile: ' . $e->getMessage());
        }
        
        return ['success' => true, 'message' => 'Rack file created successfully.', 'file_id' => $file->id];
    }
    
    private function requestPhysicalFile(Request $request)
    {
        $validated = $request->validate([
            'file_id' => 'required|integer|exists:rack_files,id',
            'purpose' => 'required|string|min:10|max:500',
            'expected_return_date' => 'nullable|date',
            'urgency' => 'required|in:low,normal,high,urgent'
        ], [
            'purpose.required' => 'The purpose field is required.',
            'purpose.min' => 'The purpose must be at least 10 characters long.',
            'purpose.max' => 'The purpose cannot exceed 500 characters.'
        ]);
        
        $file = RackFile::findOrFail($validated['file_id']);
        
        if ($file->status !== 'available') {
            throw new \Exception("This file is currently not available. Status: " . $file->status);
        }
        
        $user = Auth::user();
        
        // Check for pending request
        $existingRequest = RackFileRequest::where('file_id', $validated['file_id'])
            ->where('requested_by', $user->id)
            ->where('status', 'pending')
            ->exists();
        
        if ($existingRequest) {
            throw new \Exception("You already have a pending request for this file.");
        }
        
        $rackRequest = RackFileRequest::create([
            'file_id' => $validated['file_id'],
            'requested_by' => $user->id,
            'purpose' => $validated['purpose'],
            'expected_return_date' => $validated['expected_return_date'],
            'urgency' => $validated['urgency'],
            'status' => 'pending'
        ]);
        
        // Send notifications
        try {
            $fileName = $file->file_name . ' (' . $file->file_number . ')';
            
            // Notify requester
            $this->notificationService->notify(
                $user->id,
                "Your file request for '{$fileName}' has been submitted and is pending approval.",
                route('modules.files.physical'),
                'File Request Submitted'
            );
            
            // Notify managers (HOD/HR/CEO) about new request
            if ($user->primary_department_id) {
                $this->notificationService->notifyHOD(
                    $user->primary_department_id,
                    "New file request for '{$fileName}' from {$user->name} is pending your approval. Urgency: " . ucfirst($validated['urgency']),
                    route('modules.files.physical'),
                    'New File Request Pending Approval'
                );
            }
            
            // Also notify HR
            $this->notificationService->notifyHR(
                "New file request for '{$fileName}' from {$user->name} is pending approval. Urgency: " . ucfirst($validated['urgency']),
                route('modules.files.physical'),
                'New File Request Pending Approval'
            );
        } catch (\Exception $e) {
            \Log::error('Notification error in requestPhysicalFile: ' . $e->getMessage());
        }
        
        return ['success' => true, 'message' => 'Physical file request submitted successfully. You will be notified when it\'s processed.', 
                'request_id' => $rackRequest->id];
    }
    
    private function assignPhysicalFile(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You do not have permission to assign files.");
        }
        
        $validated = $request->validate([
            'file_id' => 'required|integer|exists:rack_files,id',
            'user_id' => 'required|integer|exists:users,id',
            'purpose' => 'required|string|min:10|max:500',
            'expected_return_date' => 'required|date|after:today',
            'urgency' => 'required|in:low,normal,high,urgent'
        ], [
            'purpose.required' => 'The purpose field is required.',
            'purpose.min' => 'The purpose must be at least 10 characters long.',
            'purpose.max' => 'The purpose cannot exceed 500 characters.',
            'expected_return_date.required' => 'The expected return date is required.',
            'expected_return_date.after' => 'The expected return date must be after today.'
        ]);
        
        $file = RackFile::findOrFail($validated['file_id']);
        
        if ($file->status !== 'available') {
            throw new \Exception("This file is currently not available. Status: " . $file->status);
        }
        
        $assignedUser = User::findOrFail($validated['user_id']);
        
        // Create and automatically approve the request (admin assignment)
        $rackRequest = RackFileRequest::create([
            'file_id' => $validated['file_id'],
            'requested_by' => $validated['user_id'],
            'purpose' => $validated['purpose'],
            'expected_return_date' => $validated['expected_return_date'],
            'urgency' => $validated['urgency'],
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'manager_notes' => 'Directly assigned by ' . $user->name
        ]);
        
        // Update file status to issued
        $file->update([
            'status' => 'issued',
            'current_holder' => $validated['user_id']
        ]);
        
        // Log activity
        $this->logRackActivity($file->folder_id, 'file_assigned', $user->id, [
            'file_id' => $file->id,
            'file_number' => $file->file_number,
            'file_name' => $file->file_name,
            'assigned_to_user_id' => $validated['user_id'],
            'assigned_to_user_name' => $assignedUser->name,
            'expected_return_date' => $validated['expected_return_date']
        ]);
        
        // Send notifications
        try {
            $fileName = $file->file_name . ' (' . $file->file_number . ')';
            $folder = RackFolder::find($file->folder_id);
            $folderName = $folder ? $folder->name : 'Unknown';
            
            // Notify assigned user
            $this->notificationService->notify(
                $validated['user_id'],
                "Physical file '{$fileName}' has been assigned to you from rack folder '{$folderName}'. Expected return date: " . date('M d, Y', strtotime($validated['expected_return_date'])),
                route('modules.files.physical'),
                'Physical File Assigned'
            );
            
            // Notify HR
            $this->notificationService->notifyHR(
                "Physical file '{$fileName}' has been assigned to {$assignedUser->name} by {$user->name}.",
                route('modules.files.physical'),
                'Physical File Assigned'
            );
        } catch (\Exception $e) {
            \Log::error('Notification error in assignPhysicalFile: ' . $e->getMessage());
        }
        
        return ['success' => true, 'message' => 'Physical file assigned successfully to ' . $assignedUser->name . '.', 
                'request_id' => $rackRequest->id];
    }
    
    private function returnPhysicalFile(Request $request)
    {
        $validated = $request->validate([
            'file_id' => 'required|integer|exists:rack_files,id',
            'return_condition' => 'required|in:excellent,good,fair,poor,damaged',
            'return_notes' => 'nullable|string'
        ]);
        
        $user = Auth::user();
        
        $file = RackFile::where('id', $validated['file_id'])
            ->where('current_holder', $user->id)
            ->where('status', 'issued')
            ->first();
        
        if (!$file) {
            throw new \Exception("File not found or not issued to you.");
        }
        
        // Check if there's already a pending return request
        $existingReturnRequest = RackFileRequest::where('file_id', $validated['file_id'])
            ->where('requested_by', $user->id)
            ->where('status', 'return_pending')
            ->exists();
        
        if ($existingReturnRequest) {
            throw new \Exception("You already have a pending return request for this file.");
        }
        
        // Create a return request record (reuse RackFileRequest with status 'return_pending')
        $returnNotes = 'Return condition: ' . ucfirst($validated['return_condition']);
        if ($validated['return_notes']) {
            $returnNotes .= '. Notes: ' . $validated['return_notes'];
        }
        
        $returnRequest = RackFileRequest::create([
            'file_id' => $validated['file_id'],
            'requested_by' => $user->id,
            'purpose' => 'File return request',
            'expected_return_date' => now(),
            'urgency' => 'normal',
            'status' => 'return_pending',
            'manager_notes' => $returnNotes
        ]);
        
        // File remains 'issued' until return is approved by HOD/HR
        
        $this->logRackActivity($file->folder_id, 'file_return_requested', $user->id, [
            'file_id' => $validated['file_id'],
            'return_condition' => $validated['return_condition'],
            'return_notes' => $validated['return_notes'],
            'return_request_id' => $returnRequest->id
        ]);
        
        // Send notifications to HOD/HR for approval
        try {
            $fileName = $file->file_name . ' (' . $file->file_number . ')';
            $folder = RackFolder::find($file->folder_id);
            $folderName = $folder ? $folder->name : 'Unknown';
            
            // Notify requester
            $this->notificationService->notify(
                $user->id,
                "Your return request for file '{$fileName}' has been submitted and is pending approval.",
                route('modules.files.physical.access-requests'),
                'Return Request Submitted'
            );
            
            // Notify HOD/HR about return request
            if ($user->department_id) {
                $this->notificationService->notifyHOD(
                    $user->department_id,
                    "File return request for '{$fileName}' from {$user->name} is pending your approval. Condition: " . ucfirst($validated['return_condition']),
                    route('modules.files.physical.access-requests'),
                    'File Return Request Pending Approval'
                );
            }
            
            // Also notify HR
            $this->notificationService->notifyHR(
                "File return request for '{$fileName}' from {$user->name} is pending approval. Condition: " . ucfirst($validated['return_condition']),
                route('modules.files.physical.access-requests'),
                'File Return Request Pending Approval'
            );
        } catch (\Exception $e) {
            \Log::error('Notification error in returnPhysicalFile: ' . $e->getMessage());
        }
        
        return ['success' => true, 'message' => 'Return request submitted successfully. It will be reviewed by HOD/HR before the file is marked as available.', 'return_request_id' => $returnRequest->id];
    }
    
    private function getMyRackRequests()
    {
        $user = Auth::user();
        
        $requests = RackFileRequest::with(['file.folder', 'approver'])
            ->where('requested_by', $user->id)
            ->orderBy('requested_at', 'desc')
            ->get();
        
        return ['success' => true, 'requests' => $requests];
    }
    
    private function getRackFolderDetails(Request $request)
    {
        $folderId = $request->input('folder_id');
        
        $folder = RackFolder::with(['category', 'creator'])
            ->findOrFail($folderId);
        
        return ['success' => true, 'folder' => $folder];
    }
    
    private function getRackFileDetails(Request $request)
    {
        $fileId = $request->input('file_id');
        
        $file = RackFile::with(['folder', 'creator', 'holder', 'folder.category', 'folder.department'])
            ->findOrFail($fileId);
        
        // Get file requests history
        $requests = RackFileRequest::with(['requester', 'approver'])
            ->where('file_id', $fileId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get current active request if file is issued
        $activeRequest = null;
        if ($file->status === 'issued') {
            $activeRequest = RackFileRequest::with(['requester', 'approver'])
                ->where('file_id', $fileId)
                ->where('status', 'approved')
                ->latest()
                ->first();
        }
        
        return [
            'success' => true, 
            'file' => $file,
            'requests' => $requests,
            'active_request' => $activeRequest
        ];
    }
    
    private function updateRackFile(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You do not have permission to update files.");
        }
        
        $validated = $request->validate([
            'file_id' => 'required|integer|exists:rack_files,id',
            'file_name' => 'required|string|max:255',
            'file_number' => 'required|string|max:255',
            'file_type' => 'required|in:general,contract,financial,legal,hr,technical',
            'confidential_level' => 'required|in:normal,confidential,strictly_confidential',
            'file_date' => 'nullable|date',
            'description' => 'nullable|string',
            'tags' => 'nullable|string|max:255',
            'retention_period' => 'nullable|integer|min:1',
            'notes' => 'nullable|string'
        ], [
            'file_name.required' => 'File name is required.',
            'file_number.required' => 'File number is required.',
            'file_type.required' => 'File type is required.',
            'confidential_level.required' => 'Confidentiality level is required.'
        ]);
        
        $file = RackFile::findOrFail($validated['file_id']);
        
        // Check if file number is unique (excluding current file)
        $existingFile = RackFile::where('file_number', $validated['file_number'])
            ->where('id', '!=', $file->id)
            ->first();
        
        if ($existingFile) {
            throw new \Exception("File number already exists. Please use a different file number.");
        }
        
        // Update file
        $file->update([
            'file_name' => $validated['file_name'],
            'file_number' => $validated['file_number'],
            'file_type' => $validated['file_type'],
            'confidential_level' => $validated['confidential_level'],
            'file_date' => $validated['file_date'] ?? $file->file_date,
            'description' => $validated['description'],
            'tags' => $validated['tags'],
            'retention_period' => $validated['retention_period'],
            'notes' => $validated['notes']
        ]);
        
        // Log activity
        $this->logRackActivity($file->folder_id, 'file_updated', $user->id, [
            'file_id' => $file->id,
            'file_number' => $file->file_number,
            'file_name' => $file->file_name
        ]);
        
        return ['success' => true, 'message' => 'File updated successfully.', 'file' => $file->fresh(['folder', 'creator', 'holder'])];
    }
    
    private function getRackFolders(Request $request)
    {
        $categoryId = $request->input('category_id', 0);
        $search = $request->input('search', '');
        
        $query = RackFolder::with(['category', 'creator'])
            ->withCount(['files']) // Count ALL files including archived
            ->withCount(['files as issued_count' => function($q) {
                $q->where('status', 'issued');
            }])
            ->withCount(['files as available_count' => function($q) {
                $q->where('status', 'available');
            }]);
            // Don't filter by status - show all folders
        
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('rack_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $folders = $query->orderBy('rack_number')->orderBy('name')->get();
        
        return ['success' => true, 'folders' => $folders];
    }
    
    private function getRackFolderContents(Request $request)
    {
        $folderId = $request->input('folder_id');
        
        $folder = RackFolder::with(['category', 'creator'])
            ->withCount(['files']) // Count ALL files including archived
            ->withCount(['files as issued_count' => function($q) {
                $q->where('status', 'issued');
            }])
            ->withCount(['files as available_count' => function($q) {
                $q->where('status', 'available');
            }])
            ->findOrFail($folderId);
        
        $files = RackFile::with(['creator', 'holder'])
            ->where('folder_id', $folderId)
            ->where('status', '!=', 'archived') // Exclude archived from display but count them in totals
            ->orderBy('file_number')
            ->orderBy('file_name')
            ->get();
        
        $activities = RackActivity::with('user')
            ->where('folder_id', $folderId)
            ->orderBy('activity_date', 'desc')
            ->take(20)
            ->get();
        
        $currentUserId = Auth::id();
        
        return [
            'success' => true,
            'folder' => $folder,
            'files' => $files,
            'activities' => $activities,
            'current_user_id' => $currentUserId,
            'stats' => [
                'total_files' => $folder->files_count ?? 0,
                'issued_files' => $folder->issued_count ?? 0,
                'available_files' => $folder->available_count ?? 0
            ]
        ];
    }
    
    private function searchRackFiles(Request $request)
    {
        $query = $request->input('query');
        $searchType = $request->input('search_type', 'all');
        $categoryId = $request->input('category_id', 0);
        
        if (empty($query)) {
            throw new \Exception("Please enter a search term.");
        }
        
        $searchQuery = RackFile::with(['folder.category', 'creator', 'holder'])
            ->where('status', '!=', 'archived');
        
        $searchPattern = "%{$query}%";
        
        if ($searchType === 'filename') {
            $searchQuery->where('file_name', 'like', $searchPattern);
        } elseif ($searchType === 'filenumber') {
            $searchQuery->where('file_number', 'like', $searchPattern);
        } elseif ($searchType === 'tags') {
            $searchQuery->where('tags', 'like', $searchPattern);
        } else {
            // Search across all fields: file_name, file_number, file_type, folder name, rack_number, category, location
            $searchQuery->where(function($q) use ($searchPattern) {
                $q->where('file_name', 'like', $searchPattern)
                  ->orWhere('file_number', 'like', $searchPattern)
                  ->orWhere('file_type', 'like', $searchPattern)
                  ->orWhere('description', 'like', $searchPattern)
                  ->orWhere('tags', 'like', $searchPattern)
                  ->orWhereHas('folder', function($folderQuery) use ($searchPattern) {
                      $folderQuery->where('name', 'like', $searchPattern)
                                  ->orWhere('rack_number', 'like', $searchPattern)
                                  ->orWhere('location', 'like', $searchPattern)
                                  ->orWhereHas('category', function($categoryQuery) use ($searchPattern) {
                                      $categoryQuery->where('name', 'like', $searchPattern);
                                  });
                  });
            });
        }
        
        if ($categoryId > 0) {
            $searchQuery->whereHas('folder', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        
        $results = $searchQuery->orderBy('file_number')->limit(50)->get();
        
        return ['success' => true, 'results' => $results];
    }
    
    private function liveSearchRackFiles(Request $request)
    {
        $query = $request->input('query', '');
        
        $searchQuery = RackFile::with(['folder.category', 'creator', 'holder'])
            ->where('status', '!=', 'archived');
        
        if (!empty($query)) {
            $searchPattern = "%{$query}%";
            // Search across all fields: file_name, file_number, file_type, folder name, rack_number, category, location
            $searchQuery->where(function($q) use ($searchPattern) {
                $q->where('file_name', 'like', $searchPattern)
                  ->orWhere('file_number', 'like', $searchPattern)
                  ->orWhere('file_type', 'like', $searchPattern)
                  ->orWhere('description', 'like', $searchPattern)
                  ->orWhere('tags', 'like', $searchPattern)
                  ->orWhereHas('folder', function($folderQuery) use ($searchPattern) {
                      $folderQuery->where('name', 'like', $searchPattern)
                                  ->orWhere('rack_number', 'like', $searchPattern)
                                  ->orWhere('location', 'like', $searchPattern)
                                  ->orWhereHas('category', function($categoryQuery) use ($searchPattern) {
                                      $categoryQuery->where('name', 'like', $searchPattern);
                                  });
                  });
            });
        }
        
        $results = $searchQuery->orderBy('file_number')->limit(100)->get();
        
        return ['success' => true, 'results' => $results];
    }
    
    private function getRackCategories()
    {
        $categories = RackCategory::where('status', 'active')->orderBy('name')->get();
        
        return ['success' => true, 'categories' => $categories];
    }
    
    private function generateRackNumber($categoryId)
    {
        $category = RackCategory::findOrFail($categoryId);
        $prefix = $category->prefix ?? 'GEN';
        $datePart = date('Ymd');
        
        $lastRack = RackFolder::where('category_id', $categoryId)
            ->where('rack_number', 'like', "{$prefix}-{$datePart}-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastRack) {
            $lastNumber = intval(substr($lastRack->rack_number, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return "{$prefix}-{$datePart}-{$newNumber}";
    }
    
    private function logRackActivity($folderId, $activityType, $userId, $details = null)
    {
        // Log to RackActivity table for rack-specific tracking
        // Only log if folder_id is provided (bulk operations may not have a specific folder)
        if ($folderId !== null) {
            try {
                RackActivity::create([
                    'folder_id' => $folderId,
                    'user_id' => $userId,
                    'activity_type' => $activityType,
                    'activity_date' => now(),
                    'details' => $details
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to log rack activity', [
                    'folder_id' => $folderId,
                    'activity_type' => $activityType,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Always log to global ActivityLog for comprehensive system tracking
        $user = User::find($userId);
        $folder = $folderId ? RackFolder::find($folderId) : null;
        $folderName = $folder ? $folder->name : 'Unknown';
        
        $description = $this->getRackActivityDescription($activityType, $folderId, $details);
        
        ActivityLogService::logAction(
            $activityType,
            $description,
            $folder,
            [
                'folder_id' => $folderId,
                'folder_name' => $folderName,
                'details' => $details,
                'user_name' => $user ? $user->name : 'Unknown',
                'timestamp' => now()->toDateTimeString()
            ]
        );
    }
    
    private function getRackActivityDescription($activityType, $folderId, $details)
    {
        $folder = $folderId ? RackFolder::find($folderId) : null;
        $folderName = $folder ? $folder->name : 'Unknown';
        
        $descriptions = [
            'created' => "Created rack folder: {$folderName}",
            'file_created' => "Created rack file" . ($details && isset($details['file_name']) ? ": {$details['file_name']}" : ''),
            'file_request_approved' => "Approved file request for rack folder: {$folderName}",
            'file_request_rejected' => "Rejected file request for rack folder: {$folderName}",
            'file_returned' => "Returned file to rack folder: {$folderName}",
        ];
        
        return $descriptions[$activityType] ?? "Rack activity: {$activityType}";
    }
    
    private function createCategory(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        // Check if user has permission to create categories
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot create rack categories.");
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rack_categories,name',
            'description' => 'nullable|string',
            'prefix' => 'required|string|max:10|unique:rack_categories,prefix'
        ]);
        
        $category = RackCategory::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'prefix' => strtoupper($validated['prefix']),
            'status' => 'active'
        ]);
        
        return ['success' => true, 'message' => 'Rack category created successfully.', 'category_id' => $category->id];
    }
    
    private function updateCategory(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        // Check if user has permission to update categories
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot update rack categories.");
        }
        
        $validated = $request->validate([
            'category_id' => 'required|integer|exists:rack_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prefix' => 'required|string|max:10',
            'status' => 'required|in:active,inactive'
        ]);
        
        $category = RackCategory::findOrFail($validated['category_id']);
        
        // Check for unique constraints
        $nameExists = RackCategory::where('name', $validated['name'])
            ->where('id', '!=', $category->id)
            ->exists();
            
        if ($nameExists) {
            throw new \Exception("Category name already exists.");
        }
        
        $prefixExists = RackCategory::where('prefix', strtoupper($validated['prefix']))
            ->where('id', '!=', $category->id)
            ->exists();
            
        if ($prefixExists) {
            throw new \Exception("Category prefix already exists.");
        }
        
        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'prefix' => strtoupper($validated['prefix']),
            'status' => $validated['status']
        ]);
        
        return ['success' => true, 'message' => 'Rack category updated successfully.'];
    }
    
    private function deleteCategory(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        // Check if user has permission to delete categories
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot delete rack categories.");
        }
        
        $validated = $request->validate([
            'category_id' => 'required|integer|exists:rack_categories,id'
        ]);
        
        $category = RackCategory::findOrFail($validated['category_id']);
        
        // Check if category has folders
        $folderCount = $category->folders()->count();
        if ($folderCount > 0) {
            throw new \Exception("Cannot delete category. It has {$folderCount} folder(s) associated with it.");
        }
        
        $category->delete();
        
        return ['success' => true, 'message' => 'Rack category deleted successfully.'];
    }
    
    /**
     * Save Physical Files Settings
     */
    private function saveSettings(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        // Check if user has permission to save settings
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to save settings.'
            ], 403);
        }
        
        $request->validate([
            'default_retention' => 'nullable|integer|min:1',
            'auto_archive_days' => 'nullable|integer|min:1',
            'enable_logging' => 'nullable|in:0,1',
            'default_access_level' => 'nullable|in:public,department,private'
        ]);
        
        // Save settings using SystemSetting model
        if ($request->has('default_retention')) {
            \App\Models\SystemSetting::setValue(
                'physical_files_default_retention',
                $request->default_retention,
                'integer',
                'Default retention period in years for physical files'
            );
        }
        
        if ($request->has('auto_archive_days')) {
            \App\Models\SystemSetting::setValue(
                'physical_files_auto_archive_days',
                $request->auto_archive_days,
                'integer',
                'Number of days before auto-archiving physical files'
            );
        }
        
        if ($request->has('enable_logging')) {
            \App\Models\SystemSetting::setValue(
                'physical_files_enable_logging',
                $request->enable_logging,
                'boolean',
                'Enable activity logging for physical files'
            );
        }
        
        if ($request->has('default_access_level')) {
            \App\Models\SystemSetting::setValue(
                'physical_files_default_access_level',
                $request->default_access_level,
                'text',
                'Default access level for new physical files'
            );
        }
        
        // Log activity
        $this->logRackActivity(null, 'settings_updated', $user->id, [
            'settings' => $request->only(['default_retention', 'auto_archive_days', 'enable_logging', 'default_access_level'])
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully!'
        ]);
    }
    
    /**
     * Update Rack Folder
     */
    private function updateRackFolder(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update rack folders.'
            ], 403);
        }
        
        $request->validate([
            'folder_id' => 'required|integer|exists:rack_folders,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:rack_categories,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'access_level' => 'nullable|in:public,department,private',
            'notes' => 'nullable|string'
        ]);
        
        $rack = RackFolder::findOrFail($request->folder_id);
        
        $oldData = [
            'name' => $rack->name,
            'description' => $rack->description,
            'location' => $rack->location
        ];
        
        $rack->update([
            'name' => $request->name,
            'description' => $request->description ?? $rack->description,
            'location' => $request->location ?? $rack->location,
            'category_id' => $request->category_id ?? $rack->category_id,
            'department_id' => $request->department_id ?? $rack->department_id,
            'access_level' => $request->access_level ?? $rack->access_level,
            'notes' => $request->notes ?? $rack->notes
        ]);
        
        $this->logRackActivity($rack->id, 'updated', $user->id, [
            'rack_name' => $rack->name,
            'changes' => array_diff_assoc($rack->toArray(), $oldData)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Rack folder updated successfully',
            'rack' => $rack->fresh(['category', 'department', 'creator'])
        ]);
    }
    
    /**
     * Download Excel Template for Bulk Rack Creation
     */
    public function downloadRackTemplate()
    {
        $filename = 'rack_bulk_import_template_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $data = [
            ['Rack Name', 'Category ID', 'Rack Range Start', 'Rack Range End', 'Access Level', 'Department ID', 'Location', 'Description', 'Notes'],
            ['HR Rack A', '1', '1', '100', 'public', '', 'Room 101', 'HR Department Rack A', 'Main storage for HR files'],
            ['Finance Rack B', '2', '1', '50', 'department', '2', 'Room 205', 'Finance Department Rack B', 'Finance documents storage'],
            ['Legal Rack C', '3', '1', '75', 'private', '', 'Room 301', 'Legal Department Rack C', 'Confidential legal files'],
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            // Add BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Handle Bulk Create Racks from Excel/CSV
     */
    private function handleBulkCreateRacksExcel(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot create racks.");
        }
        
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);
        
        $file = $request->file('excel_file');
        $extension = $file->getClientOriginalExtension();
        $created = 0;
        $errors = [];
        
        try {
            if (strtolower($extension) === 'csv') {
                // Read CSV file
                $handle = fopen($file->getRealPath(), 'r');
                if ($handle === false) {
                    throw new \Exception('Failed to open CSV file');
                }
                
                // Skip header row
                $header = fgetcsv($handle);
                if (!$header) {
                    throw new \Exception('CSV file is empty or invalid');
                }
                
                $rowIndex = 1;
                while (($row = fgetcsv($handle)) !== false) {
                    $rowIndex++;
                    
                    if (count($row) < 5) {
                        $errors[] = "Row {$rowIndex}: Insufficient columns. Required: Rack Name, Category ID, Rack Range Start, Rack Range End, Access Level";
                        continue;
                    }
                    
                    $rackName = trim($row[0] ?? '');
                    $categoryId = trim($row[1] ?? '');
                    $rangeStart = trim($row[2] ?? '');
                    $rangeEnd = trim($row[3] ?? '');
                    $accessLevel = trim($row[4] ?? 'public');
                    $departmentId = trim($row[5] ?? '');
                    $location = trim($row[6] ?? '');
                    $description = trim($row[7] ?? '');
                    $notes = trim($row[8] ?? '');
                    
                    if (empty($rackName)) {
                        $errors[] = "Row {$rowIndex}: Rack name is required";
                        continue;
                    }
                    
                    if (empty($categoryId) || !is_numeric($categoryId)) {
                        $errors[] = "Row {$rowIndex}: Valid Category ID is required";
                        continue;
                    }
                    
                    $category = RackCategory::find($categoryId);
                    if (!$category) {
                        $errors[] = "Row {$rowIndex}: Category ID {$categoryId} not found";
                        continue;
                    }
                    
                    if (empty($rangeStart) || !is_numeric($rangeStart) || $rangeStart < 1) {
                        $errors[] = "Row {$rowIndex}: Valid Rack Range Start (minimum 1) is required";
                        continue;
                    }
                    
                    if (empty($rangeEnd) || !is_numeric($rangeEnd) || $rangeEnd < 1) {
                        $errors[] = "Row {$rowIndex}: Valid Rack Range End (minimum 1) is required";
                        continue;
                    }
                    
                    if ($rangeStart > $rangeEnd) {
                        $errors[] = "Row {$rowIndex}: Rack Range Start cannot be greater than Range End";
                        continue;
                    }
                    
                    // Validate access level
                    if (!in_array($accessLevel, ['public', 'department', 'private'])) {
                        $accessLevel = 'public';
                    }
                    
                    // Validate department if access level is department
                    if ($accessLevel === 'department' && empty($departmentId)) {
                        $errors[] = "Row {$rowIndex}: Department ID required for department access level";
                        continue;
                    }
                    
                    if ($accessLevel === 'department' && !empty($departmentId)) {
                        $department = \App\Models\Department::find($departmentId);
                        if (!$department) {
                            $errors[] = "Row {$rowIndex}: Department ID {$departmentId} not found";
                            continue;
                        }
                    }
                    
                    // Check if rack name already exists
                    $existingRack = RackFolder::where('name', $rackName)->first();
                    if ($existingRack) {
                        $errors[] = "Row {$rowIndex}: Rack '{$rackName}' already exists";
                        continue;
                    }
                    
                    // Generate rack number
                    $rackNumber = $this->generateRackNumber($categoryId);
                    
                    // Create rack
                    try {
                        RackFolder::create([
                            'name' => $rackName,
                            'category_id' => $categoryId,
                            'department_id' => $accessLevel === 'department' && !empty($departmentId) ? $departmentId : null,
                            'rack_number' => $rackNumber,
                            'rack_range_start' => $rangeStart,
                            'rack_range_end' => $rangeEnd,
                            'access_level' => $accessLevel,
                            'location' => $location ?: null,
                            'description' => $description ?: null,
                            'notes' => $notes ?: null,
                            'created_by' => $user->id,
                            'status' => 'active'
                        ]);
                        
                        $created++;
                    } catch (\Exception $e) {
                        $errors[] = "Row {$rowIndex}: " . $e->getMessage();
                    }
                }
                
                fclose($handle);
            } else {
                // Try to use Laravel Excel if available
                if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
                    $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
                    if (empty($data) || empty($data[0])) {
                        throw new \Exception('Excel file is empty or invalid');
                    }
                    
                    $rows = $data[0];
                    // Skip header row
                    array_shift($rows);
                    
                    $rowIndex = 1;
                    foreach ($rows as $row) {
                        $rowIndex++;
                        
                        if (count($row) < 5) {
                            $errors[] = "Row {$rowIndex}: Insufficient columns";
                            continue;
                        }
                        
                        $rackName = trim($row[0] ?? '');
                        $categoryId = trim($row[1] ?? '');
                        $rangeStart = trim($row[2] ?? '');
                        $rangeEnd = trim($row[3] ?? '');
                        $accessLevel = trim($row[4] ?? 'public');
                        $departmentId = trim($row[5] ?? '');
                        $location = trim($row[6] ?? '');
                        $description = trim($row[7] ?? '');
                        $notes = trim($row[8] ?? '');
                        
                        if (empty($rackName)) {
                            $errors[] = "Row {$rowIndex}: Rack name is required";
                            continue;
                        }
                        
                        if (empty($categoryId) || !is_numeric($categoryId)) {
                            $errors[] = "Row {$rowIndex}: Valid Category ID is required";
                            continue;
                        }
                        
                        $category = RackCategory::find($categoryId);
                        if (!$category) {
                            $errors[] = "Row {$rowIndex}: Category ID {$categoryId} not found";
                            continue;
                        }
                        
                        if (empty($rangeStart) || !is_numeric($rangeStart) || $rangeStart < 1) {
                            $errors[] = "Row {$rowIndex}: Valid Rack Range Start is required";
                            continue;
                        }
                        
                        if (empty($rangeEnd) || !is_numeric($rangeEnd) || $rangeEnd < 1) {
                            $errors[] = "Row {$rowIndex}: Valid Rack Range End is required";
                            continue;
                        }
                        
                        if ($rangeStart > $rangeEnd) {
                            $errors[] = "Row {$rowIndex}: Rack Range Start cannot be greater than Range End";
                            continue;
                        }
                        
                        // Validate access level
                        if (!in_array($accessLevel, ['public', 'department', 'private'])) {
                            $accessLevel = 'public';
                        }
                        
                        // Validate department if access level is department
                        if ($accessLevel === 'department' && empty($departmentId)) {
                            $errors[] = "Row {$rowIndex}: Department ID required for department access level";
                            continue;
                        }
                        
                        if ($accessLevel === 'department' && !empty($departmentId)) {
                            $department = \App\Models\Department::find($departmentId);
                            if (!$department) {
                                $errors[] = "Row {$rowIndex}: Department ID {$departmentId} not found";
                                continue;
                            }
                        }
                        
                        // Check if rack name already exists
                        $existingRack = RackFolder::where('name', $rackName)->first();
                        if ($existingRack) {
                            $errors[] = "Row {$rowIndex}: Rack '{$rackName}' already exists";
                            continue;
                        }
                        
                        // Generate rack number
                        $rackNumber = $this->generateRackNumber($categoryId);
                        
                        // Create rack
                        try {
                            RackFolder::create([
                                'name' => $rackName,
                                'category_id' => $categoryId,
                                'department_id' => $accessLevel === 'department' && !empty($departmentId) ? $departmentId : null,
                                'rack_number' => $rackNumber,
                                'rack_range_start' => $rangeStart,
                                'rack_range_end' => $rangeEnd,
                                'access_level' => $accessLevel,
                                'location' => $location ?: null,
                                'description' => $description ?: null,
                                'notes' => $notes ?: null,
                                'created_by' => $user->id,
                                'status' => 'active'
                            ]);
                            
                            $created++;
                        } catch (\Exception $e) {
                            $errors[] = "Row {$rowIndex}: " . $e->getMessage();
                        }
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => 'Excel file support requires Laravel Excel package. Please convert to CSV format or install maatwebsite/excel package.'
                    ];
                }
            }
            
            $this->logRackActivity(null, 'bulk_rack_created', $user->id, [
                'created_count' => $created,
                'errors_count' => count($errors)
            ]);
            
            $message = "Successfully created {$created} rack(s)";
            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " error(s) occurred.";
            }
            
            return [
                'success' => true,
                'message' => $message,
                'created' => $created,
                'errors' => $errors,
                'errors_count' => count($errors)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to process Excel file: ' . $e->getMessage()
            ];
        }
    }
}

