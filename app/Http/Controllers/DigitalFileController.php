<?php

namespace App\Http\Controllers;

use App\Models\FileFolder;
use App\Models\File as FileModel;
use App\Models\FileUserAssignment;
use App\Models\FileAccessRequest;
use App\Models\FileActivity;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Department;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalFileController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        // Redirect to dashboard
        return redirect()->route('modules.files.digital.dashboard');
    }
    
    /**
     * Legacy index method - kept for backward compatibility
     */
    public function indexLegacy()
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
        
        // Get departments
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        // Get root folders with file counts
        $rootFoldersQuery = FileFolder::with(['creator', 'department'])
            ->withCount(['files']) // Count all files in each folder
            ->whereNull('parent_id');
        
        if (!$canViewAll) {
            $rootFoldersQuery->where(function($query) use ($currentDeptId, $user) {
                $query->where('access_level', 'public')
                    ->orWhere(function($q) use ($currentDeptId) {
                        $q->where('access_level', 'department')
                          ->where('department_id', $currentDeptId);
                    })
                    ->orWhere(function($q2) use ($user) {
                        // Include folders assigned to this user
                        $q2->whereHas('assignments', function($assignmentQuery) use ($user) {
                            $assignmentQuery->where('user_id', $user->id)
                                ->where(function($exp) {
                                    $exp->whereNull('expiry_date')
                                        ->orWhere('expiry_date', '>=', now());
                                });
                        });
                    });
            });
        }
        
        $rootFolders = $rootFoldersQuery->orderBy('name')->get();
        
        // Get recent files
        $recentFiles = FileModel::with(['uploader', 'folder'])
            ->where(function($query) use ($user, $currentDeptId, $isStaff, $canViewAll) {
                if ($canViewAll) return;
                if ($isStaff) {
                    $query->whereHas('assignments', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where(function($w) {
                              $w->whereNull('expiry_date')
                                ->orWhere('expiry_date', '>=', now());
                          });
                    });
                } else {
                    $query->where(function($q) use ($user, $currentDeptId) {
                        $q->where('uploaded_by', $user->id)
                          ->orWhere('access_level', 'public')
                          ->orWhere(function($w) use ($currentDeptId) {
                              $w->where('access_level', 'department')
                                ->where('department_id', $currentDeptId);
                          });
                    });
                }
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats($user, $canViewAll, $currentDeptId);
        
        // Get pending requests count
        $pendingRequestsCount = $canManageFiles ? 
            FileAccessRequest::where('status', 'pending')->count() : 0;
        
        return view('modules.files.digital', compact(
            'canManageFiles',
            'canViewAll',
            'isStaff',
            'departments',
            'rootFolders',
            'recentFiles',
            'stats',
            'pendingRequestsCount'
        ));
    }
    
    public function handleRequest(Request $request)
    {
        $action = $request->input('action');
        
        // Determine if this is a write operation that needs transaction
        $writeOperations = [
            'create_folder', 'upload_file', 'request_file_access', 'approve_request',
            'reject_request', 'add_user_assignment', 'remove_user_assignment',
            'bulk_upload', 'cancel_request', 'bulk_create_folders', 'update_folder',
            'delete_folder', 'delete_file', 'move_folder', 'bulk_delete_folders', 'bulk_move_folders',
            'bulk_assign_folders_to_staff', 'bulk_assign_folders_to_department',
            'assign_file_folder', 'bulk_upload_files', 'create_nested_folder', 'bulk_create_folders_excel',
            'update_file', 'get_file_details'
        ];
        
        $needsTransaction = in_array($action, $writeOperations);
        
        try {
            if ($needsTransaction) {
                DB::beginTransaction();
            }
            
            $response = null;
            
            switch ($action) {
                case 'create_folder':
                    $response = $this->handleCreateFolder($request);
                    break;
                    
                case 'upload_file':
                    $response = $this->handleUploadFile($request);
                    break;
                    
                case 'get_folder_contents':
                    $response = $this->handleGetFolderContents($request);
                    break;
                    
                case 'download_file':
                    $response = $this->handleDownloadFile($request);
                    break;
                    
                case 'get_folder_tree':
                    $response = $this->handleGetFolderTree($request);
                    break;
                case 'get_all_folders':
                    $response = $this->handleGetAllFolders($request);
                    break;
                    
                case 'request_file_access':
                    $response = $this->handleRequestFileAccess($request);
                    break;
                    
                case 'get_my_requests':
                    $response = $this->handleGetMyRequests($request);
                    break;
                    
                case 'get_pending_requests':
                    $response = $this->handleGetPendingRequests($request);
                    break;
                    
                case 'approve_request':
                    $response = $this->handleApproveRequest($request);
                    break;
                    
                case 'reject_request':
                    $response = $this->handleRejectRequest($request);
                    break;
                    
                case 'get_file_assignments':
                    $response = $this->handleGetFileAssignments($request);
                    break;
                    
                case 'add_user_assignment':
                    $response = $this->handleAddUserAssignment($request);
                    break;
                    
                case 'remove_user_assignment':
                    $response = $this->handleRemoveUserAssignment($request);
                    break;
                    
                case 'search_all':
                    $response = $this->handleSearchAll($request);
                    break;
                    
                case 'get_dashboard_stats':
                    $response = $this->handleGetDashboardStats($request);
                    break;
                    
                case 'bulk_upload':
                    $response = $this->handleBulkUpload($request);
                    break;
                    
                case 'bulk_create_folders':
                    $response = $this->handleBulkCreateFolders($request);
                    break;
                    
                case 'update_folder':
                    $response = $this->handleUpdateFolder($request);
                    break;
                    
                case 'delete_folder':
                    $response = $this->handleDeleteFolder($request);
                    break;
                    
                case 'delete_file':
                    $response = $this->handleDeleteFile($request);
                    break;
                    
                case 'move_folder':
                    $response = $this->handleMoveFolder($request);
                    break;
                    
                case 'advanced_search':
                    $response = $this->handleAdvancedSearch($request);
                    break;
                    
                case 'get_analytics':
                    $response = $this->handleGetAnalytics($request);
                    break;
                    
                case 'get_recent_activity':
                    $response = $this->handleGetRecentActivity($request);
                    break;
                    
                case 'get_users_for_assignment':
                    $response = $this->handleGetUsersForAssignment($request);
                    break;
                    
                case 'view_file_details':
                    $response = $this->handleViewFileDetails($request);
                    break;
                    
                case 'cancel_request':
                    $response = $this->handleCancelRequest($request);
                    break;
                    
                case 'assign_folder_to_staff':
                    $response = $this->handleAssignFolderToStaff($request);
                    break;
                    
                case 'assign_folder_to_department':
                    $response = $this->handleAssignFolderToDepartment($request);
                    break;
                    
                case 'get_departments':
                    $response = $this->handleGetDepartments($request);
                    break;
                    
                case 'get_folder_details':
                    $response = $this->handleGetFolderDetails($request);
                    break;
                    
                case 'bulk_delete_folders':
                    $response = $this->handleBulkDeleteFolders($request);
                    break;
                    
                case 'bulk_move_folders':
                    $response = $this->handleBulkMoveFolders($request);
                    break;
                    
                case 'bulk_assign_folders_to_staff':
                    $response = $this->handleBulkAssignFoldersToStaff($request);
                    break;
                    
                case 'bulk_assign_folders_to_department':
                    $response = $this->handleBulkAssignFoldersToDepartment($request);
                    break;
                    
                case 'assign_file_folder':
                    $response = $this->handleAssignFileFolder($request);
                    break;
                    
                case 'request_file_access':
                    $response = $this->handleRequestFileAccess($request);
                    break;
                    
                case 'bulk_upload_files':
                    $response = $this->handleBulkUploadFiles($request);
                    break;
                    
                case 'create_nested_folder':
                    $response = $this->handleCreateNestedFolder($request);
                    break;
                    
                case 'bulk_create_folders_excel':
                    $response = $this->handleBulkCreateFoldersExcel($request);
                    break;
                    
                case 'update_file':
                    $response = $this->handleUpdateFile($request);
                    break;
                    
                case 'get_file_details':
                    $response = $this->handleGetFileDetails($request);
                    break;
                    
                default:
                    $response = response()->json([
                        'success' => false,
                        'message' => 'Unknown action'
                    ]);
            }
            
            if ($needsTransaction && $response) {
                $responseData = json_decode($response->getContent(), true);
                if (isset($responseData['success']) && $responseData['success']) {
                    DB::commit();
                } else {
                    DB::rollBack();
                }
            }
            
            return $response;
            
        } catch (\Exception $e) {
            if ($needsTransaction) {
                DB::rollBack();
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function handleCreateFolder(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'folder_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'folder_code' => 'nullable|string|max:255',
            'parent_id' => 'required|integer',
            'access_level' => 'required|in:public,department,private',
            'department_id' => 'required_if:access_level,department|nullable|integer|exists:departments,id'
        ]);
        
        $folder = FileFolder::create([
            'name' => $request->folder_name,
            'folder_code' => $request->folder_code,
            'description' => $request->description,
            'parent_id' => $request->parent_id == 0 ? null : $request->parent_id,
            'path' => $this->generateFolderPath($request->parent_id),
            'created_by' => $user->id,
            'access_level' => $request->access_level,
            'department_id' => $request->access_level === 'department' ? $request->department_id : null
        ]);
        
        $this->logFileActivity(null, 'folder_created', $user->id, [
            'folder_id' => $folder->id,
            'folder_name' => $request->folder_name
        ]);
        
        // Send SMS notification
        try {
            $this->notificationService->notify(
                $user->id,
                "You have successfully created folder '{$request->folder_name}' in the digital file management system.",
                route('modules.files.digital'),
                'Folder Created Successfully'
            );
        } catch (\Exception $e) {
            \Log::error('SMS notification error in handleCreateFolder: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Folder created successfully',
            'folder_id' => $folder->id
        ]);
    }
    
    private function handleUploadFile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB
            'folder_id' => 'required|integer|exists:file_folders,id',
            'description' => 'nullable|string',
            'access_level' => 'required|in:public,department,private',
            'confidential_level' => 'required|in:normal,confidential,strictly_confidential',
            'department_id' => 'required_if:access_level,department|nullable|integer|exists:departments,id'
        ]);
        
        $folder = FileFolder::findOrFail($request->folder_id);
        $file = $request->file('file');
        
        // Generate safe filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $safeFilename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;
        
        // Store file
        $path = $file->storeAs('uploads/files', $safeFilename, 'public');
        
        $fileModel = FileModel::create([
            'folder_id' => $request->folder_id,
            'original_name' => $originalName,
            'stored_name' => $safeFilename,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'uploaded_by' => $user->id,
            'access_level' => $request->access_level,
            'department_id' => $request->access_level === 'department' ? $request->department_id : null,
            'confidential_level' => $request->confidential_level,
            'tags' => $request->tags ?? null
        ]);
        
        // Handle user assignments if private
        if ($request->access_level === 'private' && $request->has('assigned_users')) {
            foreach ($request->assigned_users as $userId) {
                FileUserAssignment::create([
                    'file_id' => $fileModel->id,
                    'user_id' => $userId,
                    'assigned_by' => $user->id,
                    'permission_level' => $request->permission_level ?? 'view'
                ]);
            }
        }
        
        $this->logFileActivity($fileModel->id, 'upload', $user->id, [
            'original_name' => $originalName,
            'file_size' => $file->getSize()
        ]);
        
        // Send SMS notification
        try {
            $fileSize = $this->formatFileSize($file->getSize());
            $this->notificationService->notify(
                $user->id,
                "File '{$originalName}' ({$fileSize}) has been successfully uploaded to folder '{$folder->name}'.",
                route('modules.files.digital'),
                'File Uploaded Successfully'
            );
        } catch (\Exception $e) {
            \Log::error('SMS notification error in handleUploadFile: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_id' => $fileModel->id
        ]);
    }
    
    private function handleGetFolderContents(Request $request)
    {
        $folderId = $request->input('folder_id', 0);
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = in_array('System Admin', $userRoles) || 
                     in_array('CEO', $userRoles) || 
                     in_array('HR Officer', $userRoles) || 
                     in_array('Record Officer', $userRoles);
        $currentDeptId = $user->department_id ?? null;
        
        // Get subfolders with file counts
        $foldersQuery = FileFolder::with(['creator', 'department'])
            ->withCount(['files']) // Count all files in each folder
            ->where(function($query) use ($folderId) {
                if ($folderId == 0) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $folderId);
                }
            });
        
        // Apply access level filtering if user can't view all
        if (!$canViewAll) {
            $foldersQuery->where(function($q) use ($currentDeptId, $user) {
                $q->where('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  })
                  // Include folders with files assigned to this user
                  ->orWhere(function($w2) use ($user) {
                      $w2->where('access_level', 'private')
                         ->whereHas('files.assignments', function($q) use ($user) {
                             $q->where('user_id', $user->id)
                               ->where(function($exp) {
                                   $exp->whereNull('expiry_date')
                                       ->orWhere('expiry_date', '>=', now());
                               });
                         });
                  });
            });
        }
        
        $folders = $foldersQuery->get();
        
        // Get files with access control
        $filesQuery = FileModel::with(['uploader', 'folder', 'assignments'])
            ->where('folder_id', $folderId);
        
        // Apply access level filtering if user can't view all
        if (!$canViewAll) {
            $filesQuery->where(function($q) use ($user, $currentDeptId) {
                $q->where('uploaded_by', $user->id)
                  ->orWhere('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  })
                  // Include files assigned to this user
                  ->orWhereHas('assignments', function($q) use ($user) {
                      $q->where('user_id', $user->id)
                        ->where(function($exp) {
                            $exp->whereNull('expiry_date')
                                ->orWhere('expiry_date', '>=', now());
                        });
                  });
            });
        }
        
        $files = $filesQuery->get();
        
        return response()->json([
            'success' => true,
            'contents' => [
                'folders' => $folders,
                'files' => $files
            ]
        ]);
    }
    
    private function handleDownloadFile(Request $request)
    {
        $fileId = $request->input('file_id');
        $file = FileModel::findOrFail($fileId);
        $user = Auth::user();
        
        // Check access
        if (!$this->hasFileAccess($file, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ]);
        }
        
        // Update download count
        $file->increment('download_count');
        
        // Log activity
        $this->logFileActivity($file->id, 'download', $user->id);
        
        // Send SMS notification
        try {
            $this->notificationService->notify(
                $user->id,
                "You have downloaded file '{$file->original_name}' from the digital file management system.",
                route('modules.files.digital'),
                'File Downloaded'
            );
        } catch (\Exception $e) {
            \Log::error('SMS notification error in handleDownloadFile: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'download_url' => Storage::disk('public')->url($file->file_path),
            'filename' => $file->original_name
        ]);
    }
    
    private function handleGetFolderTree(Request $request)
    {
        $user = Auth::user();
        $tree = $this->buildFolderTree($user, null);
        
        return response()->json([
            'success' => true,
            'tree' => $tree
        ]);
    }
    
    private function handleRequestFileAccess(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'file_id' => 'required|integer|exists:files,id',
            'purpose' => 'required|string|min:10|max:500',
            'urgency' => 'required|in:low,normal,high,urgent'
        ], [
            'purpose.required' => 'The purpose field is required.',
            'purpose.min' => 'The purpose must be at least 10 characters long.',
            'purpose.max' => 'The purpose cannot exceed 500 characters.',
            'urgency.required' => 'Please select an urgency level.',
            'urgency.in' => 'Invalid urgency level selected.'
        ]);
        
        FileAccessRequest::create([
            'file_id' => $request->file_id,
            'user_id' => $user->id,
            'purpose' => $request->purpose,
            'urgency' => $request->urgency
        ]);
        
        $this->logFileActivity($request->file_id, 'access_requested', $user->id, [
            'purpose' => $request->purpose
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Access request submitted successfully'
        ]);
    }
    
    private function handleGetMyRequests(Request $request)
    {
        $user = Auth::user();
        
        $requests = FileAccessRequest::with(['file'])
            ->where('user_id', $user->id)
            ->orderBy('requested_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'requests' => $requests
        ]);
    }
    
    private function handleGetPendingRequests(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }
        
        $requests = FileAccessRequest::with(['file.uploader', 'requester.department'])
            ->where('status', 'pending')
            ->orderByRaw("CASE urgency WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->orderBy('requested_at', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'requests' => $requests
        ]);
    }
    
    private function handleApproveRequest(Request $request)
    {
        $user = Auth::user();
        $requestId = $request->input('request_id');
        
        $accessRequest = FileAccessRequest::where('id', $requestId)
            ->where('status', 'pending')
            ->firstOrFail();
        
        // Create assignment
        FileUserAssignment::create([
            'file_id' => $accessRequest->file_id,
            'user_id' => $accessRequest->user_id,
            'assigned_by' => $user->id,
            'permission_level' => 'view'
        ]);
        
        // Update request
        $accessRequest->update([
            'status' => 'approved',
            'processed_by' => $user->id,
            'processed_at' => now()
        ]);
        
        $this->logFileActivity($accessRequest->file_id, 'access_request_approved', $user->id);
        
        // Send SMS notification to requester
        try {
            $file = FileModel::find($accessRequest->file_id);
            $fileName = $file ? $file->original_name : 'Unknown';
            $this->notificationService->notify(
                $accessRequest->user_id,
                "Your file access request for '{$fileName}' has been approved. You can now access the file.",
                route('modules.files.digital'),
                'File Access Request Approved'
            );
        } catch (\Exception $e) {
            \Log::error('SMS notification error in handleApproveRequest: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Request approved successfully'
        ]);
    }
    
    private function handleRejectRequest(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'request_id' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        
        $accessRequest = FileAccessRequest::where('id', $request->request_id)
            ->where('status', 'pending')
            ->firstOrFail();
        
        $accessRequest->update([
            'status' => 'rejected',
            'processed_by' => $user->id,
            'processed_at' => now(),
            'rejection_reason' => $request->reason
        ]);
        
        $this->logFileActivity($accessRequest->file_id, 'access_request_rejected', $user->id);
        
        // Send SMS notification to requester
        try {
            $file = FileModel::find($accessRequest->file_id);
            $fileName = $file ? $file->original_name : 'Unknown';
            $reason = $request->reason ? " Reason: {$request->reason}" : '';
            $this->notificationService->notify(
                $accessRequest->user_id,
                "Your file access request for '{$fileName}' has been rejected.{$reason}",
                route('modules.files.digital'),
                'File Access Request Rejected'
            );
        } catch (\Exception $e) {
            \Log::error('SMS notification error in handleRejectRequest: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Request rejected'
        ]);
    }
    
    private function handleGetFileAssignments(Request $request)
    {
        $fileId = $request->input('file_id');
        
        $file = FileModel::findOrFail($fileId);
        $assignments = FileUserAssignment::with(['user.department'])
            ->where('file_id', $fileId)
            ->get();
        
        $availableUsers = User::with('department')
            ->where('status', 'active')
            ->whereDoesntHave('fileAssignments', function($q) use ($fileId) {
                $q->where('file_id', $fileId);
            })
            ->get();
        
        return response()->json([
            'success' => true,
            'file' => $file,
            'assignments' => $assignments,
            'available_users' => $availableUsers
        ]);
    }
    
    private function handleAddUserAssignment(Request $request)
    {
        $request->validate([
            'file_id' => 'required|integer|exists:files,id',
            'user_id' => 'required|integer|exists:users,id',
            'permission_level' => 'required|in:view,edit,manage'
        ]);
        
        FileUserAssignment::updateOrCreate(
            ['file_id' => $request->file_id, 'user_id' => $request->user_id],
            [
                'assigned_by' => Auth::id(),
                'permission_level' => $request->permission_level,
                'assigned_at' => now()
            ]
        );
        
        $this->logFileActivity($request->file_id, 'assignment_updated', Auth::id(), [
            'added_user' => $request->user_id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Assignment added successfully'
        ]);
    }
    
    private function handleRemoveUserAssignment(Request $request)
    {
        $assignmentId = $request->input('assignment_id');
        
        $assignment = FileUserAssignment::findOrFail($assignmentId);
        $fileId = $assignment->file_id;
        
        $this->logFileActivity($fileId, 'assignment_updated', Auth::id(), [
            'removed_user' => $assignment->user_id
        ]);
        
        $assignment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Assignment removed successfully'
        ]);
    }
    
    private function handleSearchAll(Request $request)
    {
        $searchTerm = $request->input('search_term');
        $user = Auth::user();
        
        $folders = FileFolder::where('name', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%")
            ->get();
        
        $files = FileModel::where('original_name', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%")
            ->get();
        
        return response()->json([
            'success' => true,
            'results' => [
                'folders' => $folders,
                'files' => $files
            ]
        ]);
    }
    
    private function handleGetDashboardStats(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = in_array('System Admin', $userRoles) || in_array('CEO', $userRoles) || in_array('HR Officer', $userRoles) || in_array('Record Officer', $userRoles);
        $currentDeptId = $user->department_id ?? null;
        
        $stats = $this->getDashboardStats($user, $canViewAll, $currentDeptId);
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    // Helper Methods
    private function generateFolderPath($parentId)
    {
        if ($parentId == 0) return '';
        
        $path = '';
        $currentId = $parentId;
        
        while ($currentId > 0) {
            $folder = FileFolder::find($currentId);
            if (!$folder) break;
            
            $path = $folder->name . '/' . $path;
            $currentId = $folder->parent_id ?? 0;
        }
        
        return $path;
    }
    
    private function hasFileAccess($file, $user)
    {
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        if ($canViewAll) {
            return true;
        }
        
        $currentDeptId = $user->department_id ?? null;
        
        // Check if user uploaded the file
        if ($file->uploaded_by === $user->id) {
            return true;
        }
        
        // Check access level
        if ($file->access_level === 'public') {
            return true;
        }
        
        // Check department access - check both file and folder department
        if ($file->access_level === 'department') {
            $fileDeptId = $file->department_id ?? ($file->folder->department_id ?? null);
            if ($fileDeptId === $currentDeptId) {
                return true;
            }
        }
        
        // Check assignments
        $hasAssignment = $file->assignments()
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', now());
            })
            ->exists();
        
        return $hasAssignment;
    }
    
    private function buildFolderTree($user, $parentId = null)
    {
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = in_array('System Admin', $userRoles) || 
                     in_array('CEO', $userRoles) || 
                     in_array('HR Officer', $userRoles) || 
                     in_array('Record Officer', $userRoles);
        $currentDeptId = $user->department_id ?? null;
        
        $query = FileFolder::withCount(['files']) // Count all files in each folder
            ->when($parentId === null, function($q){
                $q->whereNull('parent_id');
            }, function($q) use ($parentId) {
                $q->where('parent_id', $parentId);
            });
        
        // Apply access level filtering if user can't view all
        if (!$canViewAll) {
            $query->where(function($q) use ($currentDeptId, $user) {
                $q->where('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  })
                  // Include folders with files assigned to this user
                  ->orWhere(function($w2) use ($user) {
                      $w2->where('access_level', 'private')
                         ->whereHas('files.assignments', function($q) use ($user) {
                             $q->where('user_id', $user->id)
                               ->where(function($exp) {
                                   $exp->whereNull('expiry_date')
                                       ->orWhere('expiry_date', '>=', now());
                               });
                         });
                  });
            });
        }
        
        $folders = $query->orderBy('name')->get();
        $tree = [];
        
        foreach ($folders as $folder) {
            // Calculate total files including subfolders recursively
            $totalFilesCount = $folder->files_count;
            $subfolders = $this->buildFolderTree($user, $folder->id);
            
            // Add files from subfolders
            foreach ($subfolders as $subfolder) {
                $totalFilesCount += $subfolder['files_count'] ?? 0;
            }
            
            $tree[] = [
                'id' => $folder->id,
                'name' => $folder->name,
                'access_level' => $folder->access_level,
                'parent_id' => $parentId ?? null,
                'files_count' => $totalFilesCount, // Total files including subfolders
                'direct_files_count' => $folder->files_count, // Files directly in this folder
                'subfolders' => $subfolders
            ];
        }
        
        return $tree;
    }

    private function handleGetAllFolders(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = in_array('System Admin', $userRoles) || 
                     in_array('CEO', $userRoles) || 
                     in_array('HR Officer', $userRoles) || 
                     in_array('Record Officer', $userRoles);
        $currentDeptId = $user->department_id ?? null;
        
        $query = FileFolder::select('id','name','parent_id');
        
        // Apply access level filtering if user can't view all
        if (!$canViewAll) {
            $query->where(function($q) use ($currentDeptId, $user) {
                $q->where('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  })
                  // Include folders with files assigned to this user
                  ->orWhere(function($w2) use ($user) {
                      $w2->where('access_level', 'private')
                         ->whereHas('files.assignments', function($q) use ($user) {
                             $q->where('user_id', $user->id)
                               ->where(function($exp) {
                                   $exp->whereNull('expiry_date')
                                       ->orWhere('expiry_date', '>=', now());
                               });
                         });
                  });
            });
        }
        
        $folders = $query->orderBy('name')
            ->get()
            ->map(function($f){
                return [
                    'id' => $f->id,
                    'name' => $f->name,
                    'display_name' => trim(($this->computeFolderPath($f) ? $this->computeFolderPath($f).'/' : '').$f->name, '/'),
                ];
            })
            ->sortBy('display_name')
            ->values()
            ->all();
        
        return response()->json([
            'success' => true,
            'folders' => $folders
        ]);
    }

    private function computeFolderPath(FileFolder $folder)
    {
        $segments = [];
        $current = $folder->parent_id ? FileFolder::find($folder->parent_id) : null;
        while ($current) {
            array_unshift($segments, $current->name);
            $current = $current->parent_id ? FileFolder::find($current->parent_id) : null;
        }
        return implode('/', $segments);
    }
    
    private function logFileActivity($fileId, $activityType, $userId, $details = null)
    {
        // Log to FileActivity table for file-specific tracking
        if ($fileId) {
            FileActivity::create([
                'file_id' => $fileId,
                'user_id' => $userId,
                'activity_type' => $activityType,
                'activity_date' => now(),
                'details' => $details
            ]);
        }
        
        // Also log to global ActivityLog for comprehensive system tracking
        $user = User::find($userId);
        $description = $this->getActivityDescription($activityType, $fileId, $details);
        
        $model = null;
        if ($fileId) {
            $model = FileModel::find($fileId);
        }
        
        ActivityLogService::logAction(
            $activityType,
            $description,
            $model,
            [
                'file_id' => $fileId,
                'details' => $details,
                'user_name' => $user ? $user->name : 'Unknown',
                'timestamp' => now()->toDateTimeString()
            ]
        );
    }
    
    private function getActivityDescription($activityType, $fileId, $details)
    {
        $file = $fileId ? FileModel::find($fileId) : null;
        $fileName = $file ? $file->original_name : 'Unknown';
        
        $descriptions = [
            'file_upload' => "Uploaded file: {$fileName}",
            'upload' => "Uploaded file: {$fileName}",
            'download' => "Downloaded file: {$fileName}",
            'folder_created' => "Created folder" . ($details && isset($details['folder_name']) ? ": {$details['folder_name']}" : ''),
            'access_requested' => "Requested access to file: {$fileName}",
            'access_request_approved' => "Approved access request for file: {$fileName}",
            'access_request_rejected' => "Rejected access request for file: {$fileName}",
            'assignment_updated' => "Updated file assignment for: {$fileName}",
            'request_cancelled' => "Cancelled access request for file: {$fileName}",
            'bulk_folder_created' => "Bulk created folders",
            'folder_updated' => "Updated folder",
            'folder_deleted' => "Deleted folder",
            'folder_moved' => "Moved folder",
        ];
        
        return $descriptions[$activityType] ?? "File activity: {$activityType}";
    }
    
    // Dashboard Statistics
    private function getDashboardStats($user, $canViewAll, $currentDeptId)
    {
        // Always get actual total counts - don't filter by permissions for totals
        $totalFiles = FileModel::count();
        $totalFolders = FileFolder::count();
        
        // For user-specific stats, apply filters
        $userQuery = FileModel::query();
        if (!$canViewAll) {
            $userQuery->where(function($q) use ($user, $currentDeptId) {
                $q->where('uploaded_by', $user->id)
                  ->orWhere('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  });
            });
        }
        
        $filesThisMonth = $userQuery->whereMonth('created_at', now()->month)->count();
        $publicFolders = FileFolder::where('access_level', 'public')->count();
        $storageUsed = $userQuery->sum('file_size');
        $storagePercentage = min(($storageUsed / (1024 * 1024 * 1024)) * 100, 100); // Assuming 1GB quota
        $pendingRequests = FileAccessRequest::where('status', 'pending')->count();
        $myPendingRequests = FileAccessRequest::where('user_id', $user->id)
            ->where('status', 'pending')->count();
        
        return [
            'total_files' => $totalFiles,
            'files_this_month' => $filesThisMonth,
            'total_folders' => $totalFolders,
            'public_folders' => $publicFolders,
            'storage_used' => $this->formatFileSize($storageUsed),
            'storage_percentage' => round($storagePercentage, 1),
            'pending_requests' => $pendingRequests,
            'my_pending_requests' => $myPendingRequests
        ];
    }
    
    // Handle Bulk Upload
    private function handleBulkUpload(Request $request)
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
                'message' => 'Authorization Failed: You cannot upload files.'
            ], 403);
        }
        
        // Dropzone sends files one at a time, so handle both single file and multiple files
        // Dropzone uses paramName: "files", so each file is sent as "files"
        $files = $request->file('files');
        
        // If files is null, try alternative parameter names
        if (!$files) {
            $files = $request->file('files[]');
        }
        if (!$files) {
            $files = $request->file('file');
        }
        
        // If files is a single file, convert it to an array for consistent processing
        if ($files && !is_array($files)) {
            $files = [$files];
        }
        
        // If still no files, check if it's an empty array
        if (!$files || (is_array($files) && count($files) === 0)) {
            \Log::error('Bulk upload: No files received. Request keys: ' . implode(', ', array_keys($request->all())));
            return response()->json([
                'success' => false,
                'message' => 'No files provided for upload'
            ], 400);
        }
        
        // Validate basic fields (excluding files for now)
        $validated = $request->validate([
            'folder_id' => 'required|integer|exists:file_folders,id',
            'access_level' => 'required|in:public,department,private',
            'confidential_level' => 'required|in:normal,confidential,strictly_confidential',
            'tags' => 'nullable|string',
            'department_id' => 'required_if:access_level,department|nullable|integer|exists:departments,id',
            'assigned_users' => 'required_if:access_level,private|nullable|array',
            'assigned_users.*' => 'integer|exists:users,id'
        ]);
        
        $uploadedFiles = [];
        $errors = [];
        
        // Define allowed MIME types for documents and images
        $allowedMimeTypes = [
            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
            'text/plain',
            'text/rtf',
            'application/rtf',
            'application/vnd.oasis.opendocument.text', // .odt
            'application/vnd.oasis.opendocument.spreadsheet', // .ods
            'application/vnd.oasis.opendocument.presentation', // .odp
            'text/csv',
            'application/csv',
            'application/vnd.ms-excel', // .csv as excel
            // Images
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            'image/svg+xml',
            'image/tiff',
            'image/x-icon',
            'image/vnd.microsoft.icon',
            'image/heic',
            'image/heif',
        ];
        
        // Define allowed file extensions
        $allowedExtensions = [
            // Documents
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf',
            'odt', 'ods', 'odp', 'csv',
            // Images
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'tiff', 'tif', 'ico', 'heic', 'heif'
        ];
        
        // Validate each file individually
        foreach ($files as $index => $file) {
            if (!$file) {
                \Log::error("Bulk upload: File at index {$index} is null");
                $errors[] = "File at index {$index} is null";
                continue;
            }
            
            if (!$file->isValid()) {
                $errors[] = "File {$file->getClientOriginalName()} is invalid or corrupted";
                continue;
            }
            
            // Validate file size (20MB max)
            if ($file->getSize() > 20480 * 1024) { // 20MB in bytes
                $errors[] = "File {$file->getClientOriginalName()} exceeds maximum size of 20MB";
                continue;
            }
            
            // Validate file type - check both MIME type and extension
            $mimeType = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension());
            $originalName = $file->getClientOriginalName();
            
            // Check if extension is allowed (primary check)
            $extensionAllowed = in_array($extension, $allowedExtensions);
            
            // Check if MIME type is allowed
            $mimeTypeAllowed = in_array($mimeType, $allowedMimeTypes);
            
            // File is allowed if either extension OR MIME type is in allowed list
            // This handles cases where MIME type detection might fail but extension is valid
            if (!$extensionAllowed && !$mimeTypeAllowed) {
                $errors[] = "File '{$originalName}' is not allowed. Only documents (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, RTF, CSV, ODT, ODS, ODP, etc.) and images (JPG, JPEG, PNG, GIF, BMP, WEBP, SVG, TIFF, ICO, HEIC, HEIF, etc.) are accepted.";
                continue;
            }
        }
        
        // If all files failed validation, return error
        if (!empty($errors) && count($errors) === count($files)) {
            return response()->json([
                'success' => false,
                'message' => 'All files failed validation',
                'errors' => $errors
            ], 400);
        }
        
        foreach ($files as $file) {
            // Skip files that already failed validation
            if (!$file || !$file->isValid()) {
                continue;
            }
            
            try {
                // Generate safe filename (same as single file upload)
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $safeFilename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '_' . uniqid() . '.' . $extension;
                
                // Store file (same path as single file upload)
                $path = $file->storeAs('uploads/files', $safeFilename, 'public');
                
                $fileModel = FileModel::create([
                    'folder_id' => $validated['folder_id'],
                    'original_name' => $originalName,
                    'stored_name' => $safeFilename,
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'description' => null, // Description can be added later if needed
                    'uploaded_by' => $user->id,
                    'access_level' => $validated['access_level'],
                    'department_id' => $validated['access_level'] === 'department' ? ($validated['department_id'] ?? null) : null,
                    'confidential_level' => $validated['confidential_level'],
                    'tags' => $validated['tags'] ?? null
                ]);
                
                // Handle user assignments if private access level
                if ($validated['access_level'] === 'private' && $request->has('assigned_users') && is_array($request->assigned_users)) {
                    foreach ($request->assigned_users as $userId) {
                        FileUserAssignment::updateOrCreate(
                            [
                                'file_id' => $fileModel->id,
                                'user_id' => $userId
                            ],
                            [
                                'assigned_by' => $user->id,
                                'permission_level' => 'view',
                                'assigned_at' => now()
                            ]
                        );
                    }
                }
                
                $this->logFileActivity($fileModel->id, 'file_upload', $user->id);
                $uploadedFiles[] = $fileModel;
                
            } catch (\Exception $e) {
                \Log::error('Bulk upload error for file: ' . $file->getClientOriginalName() . ' - ' . $e->getMessage());
                $errors[] = "Failed to upload {$file->getClientOriginalName()}: " . $e->getMessage();
            }
        }
        
        // For Dropzone, we return success for each individual file
        // The frontend will track all uploads and show completion message
        if (count($uploadedFiles) > 0) {
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'uploaded_file' => $uploadedFiles[0],
                'uploaded_count' => count($uploadedFiles)
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => !empty($errors) ? implode(', ', $errors) : 'Failed to upload file',
                'errors' => $errors
            ], 400);
        }
    }
    
    // Handle Advanced Search
    private function handleAdvancedSearch(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        $currentDeptId = $user->department_id ?? null;
        $searchTerm = $request->input('search_term', '');
        
        // Search Files
        $filesQuery = FileModel::with(['uploader', 'folder', 'assignments']);
        
        // Apply search filters for files
        if (!empty($searchTerm)) {
            $filesQuery->where(function($q) use ($searchTerm) {
                $q->where('original_name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('tags', 'like', "%{$searchTerm}%");
            });
        }
        
        // Search Folders
        $foldersQuery = FileFolder::with(['creator', 'department', 'parent'])
            ->withCount(['files', 'subfolders']);
        
        // Apply search filters for folders
        if (!empty($searchTerm)) {
            $foldersQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Apply access control for files
        if (!$canViewAll) {
            $filesQuery->where(function($q) use ($user, $currentDeptId) {
                $q->where('uploaded_by', $user->id)
                  ->orWhere('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  })
                  ->orWhereHas('assignments', function($a) use ($user) {
                      $a->where('user_id', $user->id)
                        ->where(function($e) {
                            $e->whereNull('expiry_date')
                              ->orWhere('expiry_date', '>=', now());
                        });
                  });
            });
        }
        
        // Apply access control for folders
        if (!$canViewAll) {
            $foldersQuery->where(function($q) use ($currentDeptId, $user) {
                $q->where('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  })
                  ->orWhereHas('assignments', function($assignmentQuery) use ($user) {
                      $assignmentQuery->where('user_id', $user->id)
                          ->where(function($exp) {
                              $exp->whereNull('expiry_date')
                                  ->orWhere('expiry_date', '>=', now());
                          });
                  });
            });
        }
        
        $query = $filesQuery;
        
        // Apply additional filters to files
        if ($request->filled('file_type')) {
            $fileType = $request->file_type;
            switch ($fileType) {
                case 'pdf':
                    $filesQuery->where('mime_type', 'like', '%pdf%');
                    break;
                case 'image':
                    $filesQuery->where('mime_type', 'like', '%image%');
                    break;
                case 'document':
                    $filesQuery->whereIn('mime_type', [
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ]);
                    break;
                case 'spreadsheet':
                    $filesQuery->whereIn('mime_type', [
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ]);
                    break;
                case 'presentation':
                    $filesQuery->whereIn('mime_type', [
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                    ]);
                    break;
                case 'archive':
                    $filesQuery->whereIn('mime_type', [
                        'application/zip',
                        'application/x-rar-compressed',
                        'application/x-7z-compressed'
                    ]);
                    break;
            }
        }
        
        if ($request->filled('folder_id')) {
            $filesQuery->where('folder_id', $request->folder_id);
        }
        
        if ($request->filled('department_id')) {
            $filesQuery->whereHas('folder', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
            $foldersQuery->where('department_id', $request->department_id);
        }
        
        if ($request->filled('access_level')) {
            $filesQuery->where('access_level', $request->access_level);
            $foldersQuery->where('access_level', $request->access_level);
        }
        
        if ($request->filled('confidential_level')) {
            $filesQuery->where('confidential_level', $request->confidential_level);
        }
        
        if ($request->filled('date_from')) {
            $filesQuery->whereDate('created_at', '>=', $request->date_from);
            $foldersQuery->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $filesQuery->whereDate('created_at', '<=', $request->date_to);
            $foldersQuery->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('tags')) {
            $tags = explode(',', $request->tags);
            foreach ($tags as $tag) {
                $filesQuery->where('tags', 'like', '%' . trim($tag) . '%');
            }
        }
        
        if ($request->filled('size_from')) {
            $filesQuery->where('file_size', '>=', $request->size_from * 1024);
        }
        
        if ($request->filled('size_to')) {
            $filesQuery->where('file_size', '<=', $request->size_to * 1024);
        }
        
        // Get results
        $files = $filesQuery->orderBy('created_at', 'desc')->get();
        $folders = $foldersQuery->orderBy('name')->get();
        
        // Format files for response
        $formattedFiles = $files->map(function($file) {
            return [
                'id' => $file->id,
                'file_id' => $file->id,
                'type' => 'file',
                'original_name' => $file->original_name,
                'name' => $file->original_name,
                'description' => $file->description,
                'file_size' => $file->file_size,
                'file_type' => $file->file_type ?? 'unknown',
                'mime_type' => $file->mime_type,
                'folder_id' => $file->folder_id,
                'folder_name' => $file->folder->name ?? 'N/A',
                'uploader_name' => $file->uploader->name ?? 'System',
                'uploaded_by' => $file->uploader->name ?? 'System',
                'created_at' => $file->created_at->format('M d, Y'),
                'access_level' => $file->access_level
            ];
        });
        
        // Format folders for response
        $formattedFolders = $folders->map(function($folder) {
            return [
                'id' => $folder->id,
                'type' => 'folder',
                'name' => $folder->name,
                'folder_name' => $folder->name,
                'description' => $folder->description,
                'files_count' => $folder->files_count ?? 0,
                'subfolders_count' => $folder->subfolders_count ?? 0,
                'department_name' => $folder->department->name ?? 'N/A',
                'created_by' => $folder->creator->name ?? 'System',
                'created_at' => $folder->created_at->format('M d, Y'),
                'access_level' => $folder->access_level
            ];
        });
        
        // Combine results
        $allResults = $formattedFiles->merge($formattedFolders);
        
        return response()->json([
            'success' => true,
            'files' => $formattedFiles->values()->all(),
            'folders' => $formattedFolders->values()->all(),
            'results' => $allResults->values()->all(),
            'count' => $allResults->count()
        ]);
    }
    
    // Handle Get Analytics
    private function handleGetAnalytics(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        $query = FileModel::query();
        
        if (!$canViewAll) {
            $currentDeptId = $user->department_id ?? null;
            $query->where(function($q) use ($user, $currentDeptId) {
                $q->where('uploaded_by', $user->id)
                  ->orWhere('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  });
            });
        }
        
        // File types distribution
        $fileTypes = $query->selectRaw('
            CASE 
                WHEN mime_type LIKE "%pdf%" THEN "PDF"
                WHEN mime_type LIKE "%image%" THEN "Image"
                WHEN mime_type LIKE "%word%" OR mime_type LIKE "%document%" THEN "Document"
                WHEN mime_type LIKE "%excel%" OR mime_type LIKE "%spreadsheet%" THEN "Spreadsheet"
                WHEN mime_type LIKE "%powerpoint%" OR mime_type LIKE "%presentation%" THEN "Presentation"
                WHEN mime_type LIKE "%zip%" OR mime_type LIKE "%archive%" THEN "Archive"
                ELSE "Other"
            END as type,
            COUNT(*) as count
        ')->groupBy('type')->get();
        
        // Storage by department
        $storageByDepartment = FileModel::join('file_folders', 'files.folder_id', '=', 'file_folders.id')
            ->join('departments', 'file_folders.department_id', '=', 'departments.id')
            ->selectRaw('departments.name as department, SUM(files.file_size) as storage')
            ->groupBy('departments.id', 'departments.name')
            ->get();
        
        // Activity timeline (last 30 days)
        $activityTimeline = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $uploads = FileModel::whereDate('created_at', $date)->count();
            $downloads = FileActivity::where('activity_type', 'file_download')
                ->whereDate('created_at', $date)->count();
            
            $activityTimeline[] = [
                'date' => $date,
                'uploads' => $uploads,
                'downloads' => $downloads
            ];
        }
        
        // Files by department
        $departmentFiles = FileModel::join('file_folders', 'files.folder_id', '=', 'file_folders.id')
            ->join('departments', 'file_folders.department_id', '=', 'departments.id')
            ->selectRaw('departments.name as department, COUNT(*) as count')
            ->groupBy('departments.id', 'departments.name')
            ->get();
        
        return response()->json([
            'success' => true,
            'analytics' => [
                'file_types' => $fileTypes,
                'storage_by_department' => $storageByDepartment,
                'activity_timeline' => $activityTimeline,
                'department_files' => $departmentFiles
            ]
        ]);
    }
    
    // Handle Get Recent Activity
    private function handleGetRecentActivity(Request $request)
    {
        $user = Auth::user();
        
        $activities = FileActivity::with(['file', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'activity_type' => $activity->activity_type,
                    'description' => $this->formatActivityDescription($activity),
                    'created_at' => $activity->created_at->format('M d, Y H:i')
                ];
            });
        
        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }
    
    // Handle Get Users for Assignment
    private function handleGetUsersForAssignment(Request $request)
    {
        $users = User::where('is_active', true)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
    
    // Handle View File Details
    private function handleViewFileDetails(Request $request)
    {
        $fileId = $request->input('file_id');
        $user = Auth::user();
        
        $file = FileModel::with(['uploader', 'folder', 'assignments.user', 'activities.user'])
            ->findOrFail($fileId);
        
        // Check access permissions
        $hasAccess = $this->checkFileAccess($file, $user);
        
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this file'
            ]);
        }
        
        $fileDetails = [
            'id' => $file->id,
            'original_name' => $file->original_name,
            'filename' => $file->filename,
            'file_size' => $this->formatFileSize($file->file_size),
            'mime_type' => $file->mime_type,
            'description' => $file->description,
            'tags' => $file->tags,
            'access_level' => $file->access_level,
            'confidential_level' => $file->confidential_level,
            'uploaded_by' => $file->uploader->name,
            'folder' => $file->folder->name,
            'created_at' => $file->created_at->format('M d, Y H:i'),
            'download_count' => $file->download_count,
            'assigned_users' => $file->assignments->map(function($assignment) {
                return [
                    'id' => $assignment->user->id,
                    'name' => $assignment->user->name,
                    'email' => $assignment->user->email,
                    'assigned_at' => $assignment->created_at->format('M d, Y'),
                    'expiry_date' => $assignment->expiry_date ? $assignment->expiry_date->format('M d, Y') : 'No expiry'
                ];
            }),
            'recent_activities' => $file->activities->take(5)->map(function($activity) {
                return [
                    'type' => $activity->activity_type,
                    'user' => $activity->user->name,
                    'date' => $activity->created_at->format('M d, Y H:i'),
                    'details' => $activity->details
                ];
            })
        ];
        
        return response()->json([
            'success' => true,
            'file' => $fileDetails
        ]);
    }
    
    // Handle Cancel Request
    private function handleCancelRequest(Request $request)
    {
        $requestId = $request->input('request_id');
        $user = Auth::user();
        
        $accessRequest = FileAccessRequest::where('id', $requestId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();
        
        $accessRequest->update(['status' => 'cancelled']);
        
        $this->logFileActivity($accessRequest->file_id, 'request_cancelled', $user->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Request cancelled successfully'
        ]);
    }
    
    // Helper Methods
    private function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
    
    private function formatActivityDescription($activity)
    {
        $file = $activity->file ?? null;
        $fileName = $file ? $file->original_name : 'Unknown';
        $details = $activity->details ?? [];
        
        $descriptions = [
            // File operations
            'file_upload' => "Uploaded file: {$fileName}",
            'upload' => "Uploaded file: {$fileName}",
            'file_download' => "Downloaded file: {$fileName}",
            'download' => "Downloaded file: {$fileName}",
            
            // Folder operations
            'folder_created' => "Created folder" . (isset($details['folder_name']) ? ": {$details['folder_name']}" : ''),
            'bulk_folder_created' => "Bulk created folders" . (isset($details['created_count']) ? " ({$details['created_count']} folders)" : ''),
            'folder_updated' => "Updated folder" . (isset($details['folder_name']) ? ": {$details['folder_name']}" : ''),
            'folder_deleted' => "Deleted folder" . (isset($details['folder_name']) ? ": {$details['folder_name']}" : ''),
            'folder_moved' => "Moved folder" . (isset($details['folder_name']) ? ": {$details['folder_name']}" : ''),
            
            // Access operations
            'access_requested' => "Requested access to file: {$fileName}",
            'access_request' => "Requested access to file: {$fileName}",
            'access_request_approved' => "Approved access request for file: {$fileName}",
            'access_request_rejected' => "Rejected access request for file: {$fileName}",
            'access_granted' => "Access granted to file: {$fileName}",
            'access_denied' => "Access denied to file: {$fileName}",
            'request_cancelled' => "Cancelled access request for file: {$fileName}",
            
            // Assignment operations
            'assignment_updated' => "Updated file assignment for: {$fileName}",
            
            // File view operations
            'file_viewed' => "Viewed file: {$fileName}",
            'view' => "Viewed file: {$fileName}",
            
            // File edit operations
            'file_updated' => "Updated file: {$fileName}",
            'file_deleted' => "Deleted file: {$fileName}",
            'file_renamed' => "Renamed file: {$fileName}",
            
            // Folder operations without file
            'created' => "Created folder",
            'updated' => "Updated folder",
            'deleted' => "Deleted folder",
        ];
        
        // If we have a description from details, use it
        if (isset($details['description'])) {
            return $details['description'];
        }
        
        // Return mapped description or a more informative default
        $description = $descriptions[$activity->activity_type] ?? null;
        
        if ($description) {
            return $description;
        }
        
        // Fallback: create a readable description from activity type
        $readableType = str_replace('_', ' ', $activity->activity_type);
        $readableType = ucwords($readableType);
        
        if ($file) {
            return "{$readableType}: {$fileName}";
        }
        
        return $readableType;
    }
    
    private function checkFileAccess($file, $user)
    {
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = in_array('System Admin', $userRoles) || 
                      in_array('CEO', $userRoles) || 
                      in_array('HR Officer', $userRoles) || 
                      in_array('Record Officer', $userRoles);
        
        if ($canViewAll) return true;
        
        if ($file->uploaded_by === $user->id) return true;
        if ($file->access_level === 'public') return true;
        
        if ($file->access_level === 'department') {
            $currentDeptId = $user->department_id ?? null;
            return $file->folder->department_id === $currentDeptId;
        }
        
        if ($file->access_level === 'private') {
            return $file->assignments()
                ->where('user_id', $user->id)
                ->where(function($q) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', now());
                })
                ->exists();
        }
        
        return false;
    }
    
    // Handle Bulk Create Folders from Excel/CSV
    private function handleBulkCreateFolders(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot create folders.");
        }
        
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);
        
        $file = $request->file('excel_file');
        $extension = $file->getClientOriginalExtension();
        $created = 0;
        $errors = [];
        
        // Get parent_id from form request (if creating within a specific folder)
        // This will override the Excel file's Parent Folder ID column
        $formParentId = $request->input('parent_id', null);
        if ($formParentId !== null) {
            $formParentId = (int)$formParentId;
            // Validate that the parent folder exists if provided
            if ($formParentId > 0) {
                $parentFolder = FileFolder::find($formParentId);
                if (!$parentFolder) {
                    return response()->json([
                        'success' => false,
                        'message' => "Parent folder with ID {$formParentId} not found"
                    ], 400);
                }
            }
        }
        
        try {
            // Open file for reading
            $handle = fopen($file->getRealPath(), 'r');
            if (!$handle) {
                throw new \Exception('Unable to open file');
            }
            
            // Read and skip header row
            $header = fgetcsv($handle); // Skip header
            
            $rowIndex = 1; // Start from 1 since we skipped header
            
            while (($data = fgetcsv($handle)) !== false) {
                $rowIndex++;
                
                if (empty(array_filter($data))) continue; // Skip empty rows
                
                // Expected columns: Folder Name, Description, Folder Code, Parent Folder ID (optional), Access Level, Department ID (optional)
                if (count($data) < 3) {
                    $errors[] = "Row {$rowIndex}: Insufficient data columns (minimum: Folder Name, Description, Folder Code)";
                    continue;
                }
                
                $folderName = trim($data[0] ?? '');
                $description = trim($data[1] ?? '');
                $folderCode = trim($data[2] ?? '');
                
                // Use form's parent_id if provided, otherwise use Excel column value
                if ($formParentId !== null) {
                    // Use the form's parent_id (ignore Excel column)
                    $parentId = $formParentId;
                } else {
                    // Use Excel column value (backward compatibility)
                    $parentId = isset($data[3]) && !empty(trim($data[3])) ? (int)trim($data[3]) : 0;
                }
                
                $accessLevel = isset($data[4]) ? trim($data[4]) : 'public';
                $departmentId = isset($data[5]) && !empty(trim($data[5])) ? (int)trim($data[5]) : null;
                
                if (empty($folderName)) {
                    $errors[] = "Row {$rowIndex}: Folder name is required";
                    continue;
                }
                
                // Validate access level
                if (!in_array($accessLevel, ['public', 'department', 'private'])) {
                    $accessLevel = 'public';
                }
                
                // Validate department if access level is department
                if ($accessLevel === 'department' && !$departmentId) {
                    $errors[] = "Row {$rowIndex}: Department ID required for department access level";
                    continue;
                }
                
                // Check if parent folder exists (only if not using form's parent_id, as it's already validated)
                if ($formParentId === null && $parentId > 0) {
                    $parentFolder = FileFolder::find($parentId);
                    if (!$parentFolder) {
                        $errors[] = "Row {$rowIndex}: Parent folder with ID {$parentId} not found";
                        continue;
                    }
                }
                
                // Check if folder already exists
                $existingFolder = FileFolder::where('name', $folderName)
                    ->where('parent_id', $parentId == 0 ? null : $parentId)
                    ->first();
                
                if ($existingFolder) {
                    $errors[] = "Row {$rowIndex}: Folder '{$folderName}' already exists";
                    continue;
                }
                
                // Create folder
                try {
                    FileFolder::create([
                        'name' => $folderName,
                        'folder_code' => $folderCode ?: null,
                        'description' => $description ?: null,
                        'parent_id' => $parentId == 0 ? null : $parentId,
                        'path' => $this->generateFolderPath($parentId),
                        'created_by' => $user->id,
                        'access_level' => $accessLevel,
                        'department_id' => $accessLevel === 'department' ? $departmentId : null
                    ]);
                    
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowIndex}: " . $e->getMessage();
                }
            }
            
            fclose($handle);
            
            $this->logFileActivity(null, 'bulk_folder_created', $user->id, [
                'created_count' => $created,
                'errors_count' => count($errors)
            ]);
            
            $message = "Successfully created {$created} folder(s)";
            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " error(s) occurred.";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'errors' => $errors,
                'errors_count' => count($errors)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process Excel file: ' . $e->getMessage()
            ]);
        }
    }
    
    // Handle Update Folder
    private function handleUpdateFolder(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot update folders.");
        }
        
        $request->validate([
            'folder_id' => 'required|integer|exists:file_folders,id',
            'folder_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'folder_code' => 'nullable|string|max:255',
            'access_level' => 'required|in:public,department,private',
            'department_id' => 'required_if:access_level,department|nullable|integer|exists:departments,id'
        ]);
        
        $folder = FileFolder::findOrFail($request->folder_id);
        
        // Check if name already exists in same parent
        $existingFolder = FileFolder::where('name', $request->folder_name)
            ->where('parent_id', $folder->parent_id)
            ->where('id', '!=', $folder->id)
            ->first();
        
        if ($existingFolder) {
            return response()->json([
                'success' => false,
                'message' => 'A folder with this name already exists in this location'
            ]);
        }
        
        $folder->update([
            'name' => $request->folder_name,
            'folder_code' => $request->folder_code,
            'description' => $request->description,
            'access_level' => $request->access_level,
            'department_id' => $request->access_level === 'department' ? $request->department_id : null
        ]);
        
        // Update path if parent changed
        if ($folder->parent_id != $request->input('parent_id', $folder->parent_id)) {
            $folder->path = $this->generateFolderPath($request->input('parent_id', $folder->parent_id));
            $folder->save();
        }
        
        $this->logFileActivity(null, 'folder_updated', $user->id, [
            'folder_id' => $folder->id,
            'folder_name' => $request->folder_name
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Folder updated successfully',
            'folder' => $folder
        ]);
    }
    
    // Handle Delete Folder
    private function handleDeleteFolder(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot delete folders.");
        }
        
        $request->validate([
            'folder_id' => 'required|integer|exists:file_folders,id'
        ]);
        
        $folder = FileFolder::with('subfolders', 'files')->findOrFail($request->folder_id);
        
        // Check if folder has subfolders or files
        $hasSubfolders = $folder->subfolders()->count() > 0;
        $hasFiles = $folder->files()->count() > 0;
        
        if ($hasSubfolders || $hasFiles) {
            $confirm = $request->input('force_delete', false);
            if (!$confirm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder contains subfolders or files. Use force_delete=true to delete anyway.',
                    'has_subfolders' => $hasSubfolders,
                    'has_files' => $hasFiles
                ]);
            }
        }
        
        $folderName = $folder->name;
        $folderId = $folder->id;
        
        // Delete subfolders recursively if force delete
        if ($request->input('force_delete', false)) {
            $this->deleteFolderRecursive($folder);
        }
        
        $folder->delete();
        
        $this->logFileActivity(null, 'folder_deleted', $user->id, [
            'folder_id' => $folderId,
            'folder_name' => $folderName
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Folder deleted successfully'
        ]);
    }
    
    // Handle Delete File
    private function handleDeleteFile(Request $request)
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
                'message' => 'You do not have permission to delete files.'
            ], 403);
        }
        
        $request->validate([
            'file_id' => 'required|integer|exists:files,id'
        ]);
        
        $file = FileModel::findOrFail($request->file_id);
        
        // Check if user owns the file or has management rights
        if ($file->uploaded_by !== $user->id && !in_array('System Admin', $userRoles) && !in_array('HR Officer', $userRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete files you uploaded.'
            ], 403);
        }
        
        $fileName = $file->original_name;
        $filePath = $file->file_path;
        $fileId = $file->id;
        
        // Delete physical file
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        
        // Delete file record
        $file->delete();
        
        // Log activity
        $this->logFileActivity($fileId, 'file_deleted', $user->id, [
            'file_name' => $fileName
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
    }
    
    // Handle Move Folder
    private function handleMoveFolder(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot move folders.");
        }
        
        $request->validate([
            'folder_id' => 'required|integer|exists:file_folders,id',
            'new_parent_id' => 'required|integer'
        ]);
        
        $folder = FileFolder::findOrFail($request->folder_id);
        $newParentId = $request->new_parent_id == 0 ? null : $request->new_parent_id;
        
        // Prevent moving folder into itself or its children
        if ($newParentId) {
            $newParent = FileFolder::findOrFail($newParentId);
            if ($this->isDescendantOf($newParent, $folder->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot move folder into its own subfolder'
                ]);
            }
        }
        
        $oldParentId = $folder->parent_id;
        $folder->parent_id = $newParentId;
        $folder->path = $this->generateFolderPath($newParentId);
        $folder->save();
        
        // Update all subfolders' paths recursively
        $this->updateFolderPaths($folder);
        
        $this->logFileActivity(null, 'folder_moved', $user->id, [
            'folder_id' => $folder->id,
            'old_parent_id' => $oldParentId,
            'new_parent_id' => $newParentId
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Folder moved successfully',
            'folder' => $folder
        ]);
    }
    
    // Helper: Delete folder recursively
    private function deleteFolderRecursive($folder)
    {
        foreach ($folder->subfolders as $subfolder) {
            $this->deleteFolderRecursive($subfolder);
            $subfolder->delete();
        }
        
        // Delete files in folder
        foreach ($folder->files as $file) {
            // Delete physical file
            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }
    }
    
    // Helper: Check if folder is descendant
    private function isDescendantOf($potentialAncestor, $folderId)
    {
        $current = $potentialAncestor;
        while ($current && $current->parent_id) {
            if ($current->parent_id == $folderId) {
                return true;
            }
            $current = FileFolder::find($current->parent_id);
            if (!$current) break;
        }
        return false;
    }
    
    // Helper: Update folder paths recursively
    private function updateFolderPaths($folder)
    {
        foreach ($folder->subfolders as $subfolder) {
            $subfolder->path = $this->generateFolderPath($subfolder->parent_id ?? $folder->id);
            $subfolder->save();
            $this->updateFolderPaths($subfolder);
        }
    }
    
    // Handle Assign Folder to Staff
    private function handleAssignFolderToStaff(Request $request)
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
                'message' => 'You do not have permission to assign folders'
            ], 403);
        }
        
        $request->validate([
            'folder_id' => 'required|integer|exists:file_folders,id',
            'user_id' => 'required|integer|exists:users,id'
        ]);
        
        $folder = FileFolder::findOrFail($request->folder_id);
        $assignedUser = User::findOrFail($request->user_id);
        
        // Update folder access level to private
        $folder->access_level = 'private';
        $folder->save();
        
        // Create assignment record for all files in the folder
        // This ensures all files in the folder are accessible to the assigned user
        $files = FileModel::where('folder_id', $folder->id)->get();
        foreach ($files as $file) {
            \App\Models\FileUserAssignment::updateOrCreate(
                [
                    'file_id' => $file->id,
                    'user_id' => $request->user_id
                ],
                [
                    'assigned_by' => $user->id,
                    'permission_level' => 'view',
                    'assigned_at' => now()
                ]
            );
        }
        
        // Log activity
        $this->logFileActivity(null, 'folder_assigned_to_staff', $user->id, [
            'folder_id' => $folder->id,
            'folder_name' => $folder->name,
            'assigned_user_id' => $request->user_id,
            'assigned_user_name' => $assignedUser->name
        ]);
        
        // Send SMS notification to assigned user
        try {
            $this->notificationService->notify(
                $request->user_id,
                "Folder Assignment: You have been assigned access to folder '{$folder->name}' by {$user->name}.",
                route('modules.files.digital'),
                'Folder Assigned'
            );
            \Log::info('SMS notification sent for folder assignment to user: ' . $request->user_id);
        } catch (\Exception $e) {
            \Log::error('SMS notification error in handleAssignFolderToStaff: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => "Folder '{$folder->name}' has been assigned to {$assignedUser->name}",
            'folder' => $folder->load('department', 'creator')
        ]);
    }
    
    // Handle Assign Folder to Department
    private function handleAssignFolderToDepartment(Request $request)
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
                'message' => 'You do not have permission to assign folders'
            ], 403);
        }
        
        $request->validate([
            'folder_id' => 'required|integer|exists:file_folders,id',
            'department_id' => 'required|integer|exists:departments,id'
        ]);
        
        $folder = FileFolder::findOrFail($request->folder_id);
        $department = Department::findOrFail($request->department_id);
        
        // Update folder department and access level
        $folder->department_id = $request->department_id;
        $folder->access_level = 'department';
        $folder->save();
        
        // Log activity
        $this->logFileActivity(null, 'folder_assigned_to_department', $user->id, [
            'folder_id' => $folder->id,
            'folder_name' => $folder->name,
            'department_id' => $request->department_id,
            'department_name' => $department->name
        ]);
        
        // Send SMS notification to department head if exists
        try {
            $departmentHead = $department->head;
            if ($departmentHead) {
                $this->notificationService->notify(
                    $departmentHead->id,
                    "Folder Assignment: Folder '{$folder->name}' has been assigned to your department ({$department->name}) by {$user->name}.",
                    route('modules.files.digital'),
                    'Folder Assigned to Department'
                );
                \Log::info('SMS notification sent for folder assignment to department head: ' . $departmentHead->id);
            }
        } catch (\Exception $e) {
            \Log::error('SMS notification error in handleAssignFolderToDepartment: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => "Folder '{$folder->name}' has been assigned to {$department->name} department",
            'folder' => $folder->load('department', 'creator')
        ]);
    }
    
    // Handle Get Departments
    private function handleGetDepartments(Request $request)
    {
        $departments = Department::where('is_active', true)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'departments' => $departments
        ]);
    }
    
    // Handle Get Folder Details
    private function handleGetFolderDetails(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|integer|exists:file_folders,id'
        ]);
        
        $folder = FileFolder::with(['department', 'creator', 'files'])
            ->withCount(['files'])
            ->findOrFail($request->folder_id);
        
        return response()->json([
            'success' => true,
            'folder' => $folder
        ]);
    }
    
    // Handle Bulk Delete Folders
    private function handleBulkDeleteFolders(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot delete folders.");
        }
        
        $request->validate([
            'folder_ids' => 'required|array',
            'folder_ids.*' => 'integer|exists:file_folders,id'
        ]);
        
        $folderIds = $request->folder_ids;
        $forceDelete = $request->input('force_delete', false);
        $deleted = 0;
        $errors = [];
        
        foreach ($folderIds as $folderId) {
            try {
                $folder = FileFolder::with('subfolders', 'files')->find($folderId);
                if (!$folder) {
                    $errors[] = "Folder ID {$folderId} not found";
                    continue;
                }
                
                $hasSubfolders = $folder->subfolders()->count() > 0;
                $hasFiles = $folder->files()->count() > 0;
                
                if (($hasSubfolders || $hasFiles) && !$forceDelete) {
                    $errors[] = "Folder '{$folder->name}' contains subfolders or files. Use force_delete=true to delete anyway.";
                    continue;
                }
                
                $folderName = $folder->name;
                
                // Delete subfolders recursively if force delete
                if ($forceDelete) {
                    $this->deleteFolderRecursive($folder);
                }
                
                $folder->delete();
                $deleted++;
                
                $this->logFileActivity(null, 'folder_deleted', $user->id, [
                    'folder_id' => $folderId,
                    'folder_name' => $folderName
                ]);
            } catch (\Exception $e) {
                $errors[] = "Error deleting folder ID {$folderId}: " . $e->getMessage();
            }
        }
        
        $message = "Successfully deleted {$deleted} folder(s)";
        if (count($errors) > 0) {
            $message .= ". " . count($errors) . " error(s) occurred.";
        }
        
        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'deleted_count' => $deleted,
            'errors' => $errors
        ]);
    }
    
    // Handle Bulk Move Folders
    private function handleBulkMoveFolders(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot move folders.");
        }
        
        $request->validate([
            'folder_ids' => 'required|array',
            'folder_ids.*' => 'integer|exists:file_folders,id',
            'new_parent_id' => 'required|integer'
        ]);
        
        $folderIds = $request->folder_ids;
        $newParentId = $request->new_parent_id == 0 ? null : $request->new_parent_id;
        
        // Validate new parent exists if not root
        if ($newParentId) {
            $newParent = FileFolder::find($newParentId);
            if (!$newParent) {
                return response()->json([
                    'success' => false,
                    'message' => 'New parent folder not found'
                ]);
            }
        }
        
        $moved = 0;
        $errors = [];
        
        foreach ($folderIds as $folderId) {
            try {
                $folder = FileFolder::find($folderId);
                if (!$folder) {
                    $errors[] = "Folder ID {$folderId} not found";
                    continue;
                }
                
                // Prevent moving folder into itself or its children
                if ($newParentId) {
                    if ($this->isDescendantOf(FileFolder::find($newParentId), $folder->id)) {
                        $errors[] = "Cannot move folder '{$folder->name}' into its own subfolder";
                        continue;
                    }
                }
                
                $oldParentId = $folder->parent_id;
                $folder->parent_id = $newParentId;
                $folder->path = $this->generateFolderPath($newParentId);
                $folder->save();
                
                // Update all subfolders' paths recursively
                $this->updateFolderPaths($folder);
                
                $moved++;
                
                $this->logFileActivity(null, 'folder_moved', $user->id, [
                    'folder_id' => $folder->id,
                    'old_parent_id' => $oldParentId,
                    'new_parent_id' => $newParentId
                ]);
            } catch (\Exception $e) {
                $errors[] = "Error moving folder ID {$folderId}: " . $e->getMessage();
            }
        }
        
        $message = "Successfully moved {$moved} folder(s)";
        if (count($errors) > 0) {
            $message .= ". " . count($errors) . " error(s) occurred.";
        }
        
        return response()->json([
            'success' => $moved > 0,
            'message' => $message,
            'moved_count' => $moved,
            'errors' => $errors
        ]);
    }
    
    // Handle Bulk Assign Folders to Staff
    private function handleBulkAssignFoldersToStaff(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot assign folders.");
        }
        
        $request->validate([
            'folder_ids' => 'required|array',
            'folder_ids.*' => 'integer|exists:file_folders,id',
            'user_id' => 'required|integer|exists:users,id'
        ]);
        
        $folderIds = $request->folder_ids;
        $userId = $request->user_id;
        $assignedUser = \App\Models\User::findOrFail($userId);
        
        $assigned = 0;
        $errors = [];
        
        foreach ($folderIds as $folderId) {
            try {
                $folder = FileFolder::find($folderId);
                if (!$folder) {
                    $errors[] = "Folder ID {$folderId} not found";
                    continue;
                }
                
                // Check if assignment already exists
                $existing = FileUserAssignment::where('folder_id', $folderId)
                    ->where('user_id', $userId)
                    ->first();
                
                if (!$existing) {
                    FileUserAssignment::create([
                        'folder_id' => $folderId,
                        'user_id' => $userId,
                        'assigned_by' => $user->id,
                        'permission_level' => 'view'
                    ]);
                    $assigned++;
                }
                
                $this->logFileActivity(null, 'folder_assigned_to_staff', $user->id, [
                    'folder_id' => $folderId,
                    'user_id' => $userId
                ]);
            } catch (\Exception $e) {
                $errors[] = "Error assigning folder ID {$folderId}: " . $e->getMessage();
            }
        }
        
        // Send notification
        try {
            $this->notificationService->notify(
                $userId,
                "You have been assigned to {$assigned} folder(s) by {$user->name}.",
                route('modules.files.digital'),
                'Folders Assigned'
            );
        } catch (\Exception $e) {
            \Log::error('Notification error in bulk assign folders to staff: ' . $e->getMessage());
        }
        
        $message = "Successfully assigned {$assigned} folder(s) to {$assignedUser->name}";
        if (count($errors) > 0) {
            $message .= ". " . count($errors) . " error(s) occurred.";
        }
        
        return response()->json([
            'success' => $assigned > 0,
            'message' => $message,
            'assigned_count' => $assigned,
            'errors' => $errors
        ]);
    }
    
    // Handle Bulk Assign Folders to Department
    private function handleBulkAssignFoldersToDepartment(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (!in_array('System Admin', $userRoles) && 
            !in_array('HR Officer', $userRoles) && 
            !in_array('HOD', $userRoles) && 
            !in_array('CEO', $userRoles) &&
            !in_array('Record Officer', $userRoles)) {
            throw new \Exception("Authorization Failed: You cannot assign folders.");
        }
        
        $request->validate([
            'folder_ids' => 'required|array',
            'folder_ids.*' => 'integer|exists:file_folders,id',
            'department_id' => 'required|integer|exists:departments,id'
        ]);
        
        $folderIds = $request->folder_ids;
        $departmentId = $request->department_id;
        $department = Department::findOrFail($departmentId);
        
        $assigned = 0;
        $errors = [];
        
        foreach ($folderIds as $folderId) {
            try {
                $folder = FileFolder::find($folderId);
                if (!$folder) {
                    $errors[] = "Folder ID {$folderId} not found";
                    continue;
                }
                
                $folder->department_id = $departmentId;
                $folder->access_level = 'department';
                $folder->save();
                
                $assigned++;
                
                $this->logFileActivity(null, 'folder_assigned_to_department', $user->id, [
                    'folder_id' => $folderId,
                    'department_id' => $departmentId
                ]);
            } catch (\Exception $e) {
                $errors[] = "Error assigning folder ID {$folderId}: " . $e->getMessage();
            }
        }
        
        // Send notification to department head
        try {
            $departmentHead = $department->head;
            if ($departmentHead) {
                $this->notificationService->notify(
                    $departmentHead->id,
                    "{$assigned} folder(s) have been assigned to your department ({$department->name}) by {$user->name}.",
                    route('modules.files.digital'),
                    'Folders Assigned to Department'
                );
            }
        } catch (\Exception $e) {
            \Log::error('Notification error in bulk assign folders to department: ' . $e->getMessage());
        }
        
        $message = "Successfully assigned {$assigned} folder(s) to {$department->name} department";
        if (count($errors) > 0) {
            $message .= ". " . count($errors) . " error(s) occurred.";
        }
        
        return response()->json([
            'success' => $assigned > 0,
            'message' => $message,
            'assigned_count' => $assigned,
            'errors' => $errors
        ]);
    }
    
    /**
     * Dashboard - Main page showing all folders
     */
    public function dashboard()
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
        
        // Get all folders (hierarchical)
        $foldersQuery = FileFolder::with(['creator', 'department', 'parent'])
            ->withCount(['files', 'subfolders']);
        
        if (!$canViewAll) {
            $foldersQuery->where(function($query) use ($currentDeptId) {
                $query->where('access_level', 'public')
                    ->orWhere(function($q) use ($currentDeptId) {
                        $q->where('access_level', 'department')
                          ->where('department_id', $currentDeptId);
                    });
            });
        }
        
        $allFolders = $foldersQuery->orderBy('name')->get();
        
        // Organize folders hierarchically
        $foldersByParent = $allFolders->groupBy('parent_id');
        $rootFolders = $foldersByParent->get(null, collect());
        
        // Get statistics
        $stats = $this->getDashboardStats($user, $canViewAll, $currentDeptId);
        
        // Get recent activity
        $recentActivity = FileActivity::with(['user', 'file.folder'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('modules.files.digital.dashboard', compact(
            'rootFolders',
            'foldersByParent',
            'allFolders',
            'stats',
            'recentActivity',
            'canManageFiles',
            'canViewAll',
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
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        // Get all folders for selection
        $folders = FileFolder::with(['department'])
            ->orderBy('name')
            ->get();
        
        return view('modules.files.digital.upload', compact('folders', 'departments', 'user'));
    }
    
    /**
     * Manage Files & Folders Page
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
            abort(403, 'You do not have permission to manage files.');
        }
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        // Get all folders
        $folders = FileFolder::with(['creator', 'department', 'parent'])
            ->withCount(['files', 'subfolders'])
            ->orderBy('name')
            ->get();
        
        // Get all files
        $files = FileModel::with(['uploader', 'folder'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('modules.files.digital.manage', compact('folders', 'files', 'departments', 'user'));
    }
    
    /**
     * Search Files Page
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
        
        // Get folders for filtering
        $folders = FileFolder::orderBy('name')->get();
        
        // Get departments for filtering
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.files.digital.search', compact('folders', 'departments', 'canViewAll', 'currentDeptId', 'user'));
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
        
        // Get comprehensive statistics
        $stats = $this->getDashboardStats($user, $canViewAll, $currentDeptId);
        
        // Build base query with access control
        $filesQuery = FileModel::query();
        if (!$canViewAll) {
            $filesQuery->where(function($q) use ($user, $currentDeptId) {
                $q->where('uploaded_by', $user->id)
                  ->orWhere('access_level', 'public')
                  ->orWhere(function($w) use ($currentDeptId) {
                      $w->where('access_level', 'department')
                        ->where('department_id', $currentDeptId);
                  });
            });
        }
        
        // Get file type distribution - derive from mime_type
        $fileTypes = (clone $filesQuery)->select(
                DB::raw("CASE 
                    WHEN mime_type LIKE 'application/pdf' THEN 'PDF'
                    WHEN mime_type LIKE 'image/%' THEN 'Image'
                    WHEN mime_type LIKE 'application/msword' OR mime_type LIKE 'application/vnd.openxmlformats-officedocument.wordprocessingml%' THEN 'Document'
                    WHEN mime_type LIKE 'application/vnd.ms-excel' OR mime_type LIKE 'application/vnd.openxmlformats-officedocument.spreadsheetml%' THEN 'Spreadsheet'
                    WHEN mime_type LIKE 'application/vnd.ms-powerpoint' OR mime_type LIKE 'application/vnd.openxmlformats-officedocument.presentationml%' THEN 'Presentation'
                    WHEN mime_type LIKE 'text/%' THEN 'Text'
                    WHEN mime_type LIKE 'application/zip' OR mime_type LIKE 'application/x-rar%' OR mime_type LIKE 'application/x-7z%' THEN 'Archive'
                    WHEN mime_type LIKE 'video/%' THEN 'Video'
                    WHEN mime_type LIKE 'audio/%' THEN 'Audio'
                    ELSE 'Other'
                END as file_type"),
                DB::raw('count(*) as count'),
                DB::raw('sum(file_size) as total_size')
            )
            ->groupBy('file_type')
            ->orderBy('count', 'desc')
            ->get();
        
        // Access level distribution
        $accessLevelStats = (clone $filesQuery)->select(
                'access_level',
                DB::raw('count(*) as count'),
                DB::raw('sum(file_size) as total_size')
            )
            ->groupBy('access_level')
            ->get();
        
        // Confidentiality level distribution
        $confidentialityStats = (clone $filesQuery)->select(
                'confidential_level',
                DB::raw('count(*) as count'),
                DB::raw('sum(file_size) as total_size')
            )
            ->whereNotNull('confidential_level')
            ->groupBy('confidential_level')
            ->get();
        
        // Department-wise statistics
        $departmentStats = FileModel::join('file_folders', 'files.folder_id', '=', 'file_folders.id')
            ->leftJoin('departments', 'file_folders.department_id', '=', 'departments.id')
            ->select(
                'departments.name as department_name',
                'departments.id as department_id',
                DB::raw('count(files.id) as file_count'),
                DB::raw('sum(files.file_size) as total_size'),
                DB::raw('count(distinct file_folders.id) as folder_count')
            )
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('file_count', 'desc')
            ->get();
        
        // Most active users (by uploads)
        $topUploaders = FileModel::join('users', 'files.uploaded_by', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('count(files.id) as upload_count'),
                DB::raw('sum(files.file_size) as total_size')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('upload_count', 'desc')
            ->limit(10)
            ->get();
        
        // Most downloaded files
        $topDownloaded = FileModel::select(
                'id',
                'original_name',
                'download_count',
                'file_size',
                'mime_type'
            )
            ->where('download_count', '>', 0)
            ->orderBy('download_count', 'desc')
            ->limit(10)
            ->get();
        
        // Recent uploads (last 7 days)
        $recentUploads = FileModel::with(['uploader', 'folder'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Activity type breakdown
        $activityTypes = FileActivity::select(
                'activity_type',
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('activity_type')
            ->orderBy('count', 'desc')
            ->get();
        
        // Activity over time (last 30 days) - detailed
        $activityData = FileActivity::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw("SUM(CASE WHEN activity_type = 'file_uploaded' THEN 1 ELSE 0 END) as uploads"),
                DB::raw("SUM(CASE WHEN activity_type = 'file_downloaded' THEN 1 ELSE 0 END) as downloads"),
                DB::raw("SUM(CASE WHEN activity_type = 'file_viewed' THEN 1 ELSE 0 END) as views")
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Weekly activity summary
        $weeklyActivity = FileActivity::select(
                DB::raw('YEARWEEK(created_at) as week'),
                DB::raw('count(*) as count'),
                DB::raw("SUM(CASE WHEN activity_type = 'file_uploaded' THEN 1 ELSE 0 END) as uploads"),
                DB::raw("SUM(CASE WHEN activity_type = 'file_downloaded' THEN 1 ELSE 0 END) as downloads")
            )
            ->where('created_at', '>=', now()->subWeeks(12))
            ->groupBy('week')
            ->orderBy('week')
            ->get();
        
        // Folder statistics with size
        $folderStats = FileFolder::withCount(['files'])
            ->select('file_folders.*')
            ->selectRaw('COALESCE(SUM(files.file_size), 0) as total_size')
            ->leftJoin('files', 'file_folders.id', '=', 'files.folder_id')
            ->groupBy('file_folders.id', 'file_folders.name', 'file_folders.created_at', 'file_folders.updated_at', 'file_folders.parent_id', 'file_folders.path', 'file_folders.access_level', 'file_folders.department_id', 'file_folders.created_by', 'file_folders.folder_code', 'file_folders.description')
            ->orderBy('total_size', 'desc')
            ->limit(20)
            ->get();
        
        // Access request statistics
        $accessRequestStats = FileAccessRequest::select(
                'status',
                DB::raw('count(*) as count')
            )
            ->groupBy('status')
            ->get();
        
        // Assignment statistics
        $assignmentStats = FileUserAssignment::select(
                DB::raw('count(*) as total_assignments'),
                DB::raw('count(distinct file_id) as files_assigned'),
                DB::raw('count(distinct user_id) as users_assigned')
            )
            ->first();
        
        // Storage growth over time (monthly)
        $storageGrowth = FileModel::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('sum(file_size) as total_size'),
                DB::raw('count(*) as file_count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Average file size by type
        $avgFileSizeByType = (clone $filesQuery)->select(
                DB::raw("CASE 
                    WHEN mime_type LIKE 'application/pdf' THEN 'PDF'
                    WHEN mime_type LIKE 'image/%' THEN 'Image'
                    WHEN mime_type LIKE 'application/msword' OR mime_type LIKE 'application/vnd.openxmlformats-officedocument.wordprocessingml%' THEN 'Document'
                    WHEN mime_type LIKE 'application/vnd.ms-excel' OR mime_type LIKE 'application/vnd.openxmlformats-officedocument.spreadsheetml%' THEN 'Spreadsheet'
                    ELSE 'Other'
                END as file_type"),
                DB::raw('avg(file_size) as avg_size'),
                DB::raw('min(file_size) as min_size'),
                DB::raw('max(file_size) as max_size')
            )
            ->groupBy('file_type')
            ->get();
        
        return view('modules.files.digital.analytics', compact(
            'stats',
            'fileTypes',
            'folderStats',
            'activityData',
            'canViewAll',
            'user',
            'accessLevelStats',
            'confidentialityStats',
            'departmentStats',
            'topUploaders',
            'topDownloaded',
            'recentUploads',
            'activityTypes',
            'weeklyActivity',
            'accessRequestStats',
            'assignmentStats',
            'storageGrowth',
            'avgFileSizeByType'
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
        $pendingRequests = FileAccessRequest::with(['user', 'file.folder'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get user's own requests
        $myRequests = FileAccessRequest::with(['user', 'file.folder'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('modules.files.digital.access-requests', compact(
            'pendingRequests',
            'myRequests',
            'canManageFiles',
            'user'
        ));
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
        
        // Get activities
        $activities = FileActivity::with(['user', 'file.folder'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('modules.files.digital.activity-log', compact('activities', 'canViewAll', 'user'));
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
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        // Get storage statistics
        $totalStorage = FileModel::sum('file_size');
        $storageLimit = config('filesystems.max_storage', 10737418240); // 10GB default
        
        return view('modules.files.digital.settings', compact(
            'departments',
            'totalStorage',
            'storageLimit',
            'user'
        ));
    }
    
    /**
     * Folder Detail Page - Independent page for each folder
     */
    public function folderDetail($folderId)
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
        
        // Get folder with relationships
        $folder = FileFolder::with(['creator', 'department', 'parent', 'subfolders.creator', 'subfolders.department'])
            ->withCount(['files', 'subfolders'])
            ->findOrFail($folderId);
        
        // Check access
        if (!$canViewAll) {
            if ($folder->access_level === 'private') {
                // Check if user has assignment
                $hasAssignment = FileUserAssignment::where('folder_id', $folderId)
                    ->where('user_id', $user->id)
                    ->where(function($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', now());
                    })
                    ->exists();
                
                if (!$hasAssignment) {
                    abort(403, 'You do not have access to this folder. Please request access.');
                }
            } elseif ($folder->access_level === 'department' && $folder->department_id !== $currentDeptId) {
                abort(403, 'You do not have access to this folder.');
            }
        }
        
        // Get files in this folder
        $files = FileModel::with(['uploader', 'assignments.user'])
            ->where('folder_id', $folderId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get subfolders
        $subfolders = FileFolder::with(['creator', 'department'])
            ->withCount(['files', 'subfolders'])
            ->where('parent_id', $folderId)
            ->orderBy('name')
            ->get();
        
        // Get breadcrumb path
        $breadcrumbs = $this->getFolderBreadcrumbs($folder);
        
        // Get folder assignments
        $folderAssignments = FileUserAssignment::with('user')
            ->where('folder_id', $folderId)
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            })
            ->get();
        
        // Get departments and users for assignment
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $users = User::with('roles')->where('is_active', true)->orderBy('name')->get();
        
        return view('modules.files.digital.folder-detail', compact(
            'folder',
            'files',
            'subfolders',
            'breadcrumbs',
            'folderAssignments',
            'canManageFiles',
            'canViewAll',
            'departments',
            'users',
            'user'
        ));
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
        
        // Get all folders and files
        $folders = FileFolder::with(['department', 'creator'])
            ->orderBy('name')
            ->get();
        
        $files = FileModel::with(['folder', 'uploader'])
            ->orderBy('original_name')
            ->get();
        
        // Get users
        $users = User::with('roles', 'department')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get departments
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.files.digital.assign', compact(
            'folders',
            'files',
            'users',
            'departments',
            'user'
        ));
    }
    
    /**
     * Get folder breadcrumbs
     */
    private function getFolderBreadcrumbs($folder)
    {
        $breadcrumbs = [];
        $current = $folder;
        
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }
        
        return $breadcrumbs;
    }
    
    /**
     * Handle Assign File/Folder with Expiry Date
     */
    private function handleAssignFileFolder(Request $request)
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
                'message' => 'You do not have permission to assign files/folders.'
            ], 403);
        }
        
        $request->validate([
            'type' => 'required|in:file,folder',
            'item_id' => 'required|integer',
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
            'expiry_duration' => 'nullable|in:1week,2weeks,4weeks,1month,3months,6months,1year,never',
            'expiry_date' => 'nullable|date|after:today',
            'permission_level' => 'nullable|in:view,download,edit'
        ]);
        
        $userIds = $request->user_ids;
        $expiryDate = null;
        
        // Calculate expiry date based on duration
        if ($request->expiry_duration && $request->expiry_duration !== 'never') {
            $durations = [
                '1week' => now()->addWeek(),
                '2weeks' => now()->addWeeks(2),
                '4weeks' => now()->addWeeks(4),
                '1month' => now()->addMonth(),
                '3months' => now()->addMonths(3),
                '6months' => now()->addMonths(6),
                '1year' => now()->addYear()
            ];
            $expiryDate = $durations[$request->expiry_duration] ?? null;
        } elseif ($request->expiry_date) {
            $expiryDate = \Carbon\Carbon::parse($request->expiry_date);
        }
        
        $assigned = 0;
        $errors = [];
        
        foreach ($userIds as $userId) {
            try {
                if ($request->type === 'file') {
                    $assignment = FileUserAssignment::updateOrCreate(
                        [
                            'file_id' => $request->item_id,
                            'user_id' => $userId
                        ],
                        [
                            'permission_level' => $request->permission_level ?? 'view',
                            'expiry_date' => $expiryDate,
                            'assigned_by' => $user->id
                        ]
                    );
                } else {
                    $assignment = FileUserAssignment::updateOrCreate(
                        [
                            'folder_id' => $request->item_id,
                            'user_id' => $userId
                        ],
                        [
                            'permission_level' => $request->permission_level ?? 'view',
                            'expiry_date' => $expiryDate,
                            'assigned_by' => $user->id
                        ]
                    );
                }
                
                $assigned++;
                
                // Log activity
                $this->logFileActivity(
                    $request->type === 'file' ? $request->item_id : null,
                    $request->type === 'file' ? 'file_assigned' : 'folder_assigned',
                    $user->id,
                    [
                        'user_id' => $userId,
                        'expiry_date' => $expiryDate?->format('Y-m-d')
                    ]
                );
                
                // Send notification
                $assignedUser = User::find($userId);
                if ($assignedUser) {
                    $itemName = $request->type === 'file' 
                        ? FileModel::find($request->item_id)->original_name 
                        : FileFolder::find($request->item_id)->name;
                    
                    $this->notificationService->notify(
                        $userId,
                        "You have been granted access to {$request->type} '{$itemName}'" . 
                        ($expiryDate ? " until {$expiryDate->format('M d, Y')}" : ' (no expiry)'),
                        route('modules.files.digital.dashboard'),
                        'File/Folder Access Granted'
                    );
                }
            } catch (\Exception $e) {
                $errors[] = "Error assigning to user ID {$userId}: " . $e->getMessage();
            }
        }
        
        return response()->json([
            'success' => $assigned > 0,
            'message' => "Successfully assigned to {$assigned} user(s)",
            'assigned_count' => $assigned,
            'errors' => $errors
        ]);
    }
    
    /**
     * Handle Bulk Upload Files
     */
    private function handleBulkUploadFiles(Request $request)
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
                'message' => 'You do not have permission to upload files.'
            ], 403);
        }
        
        $request->validate([
            'folder_id' => 'required|integer|exists:file_folders,id',
            'files' => 'required|array',
            'files.*' => 'file|max:20480', // 20MB max per file
            'access_level' => 'nullable|in:public,department,private',
            'confidential_level' => 'nullable|in:normal,confidential,strictly_confidential'
        ]);
        
        $uploaded = 0;
        $errors = [];
        
        $files = $request->hasFile('files') ? $request->file('files') : [];
        
        foreach ($files as $file) {
            try {
                $user = Auth::user();
                
                // Generate safe filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $safeFilename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                
                // Store file
                $path = $file->storeAs('uploads/files', $safeFilename, 'public');
                
                $fileModel = FileModel::create([
                    'folder_id' => $request->folder_id,
                    'original_name' => $originalName,
                    'stored_name' => $safeFilename,
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'description' => $request->description ?? null,
                    'uploaded_by' => $user->id,
                    'access_level' => $request->access_level ?? 'private',
                    'department_id' => ($request->access_level === 'department' && $request->department_id) ? $request->department_id : null,
                    'confidential_level' => $request->confidential_level ?? 'normal',
                    'tags' => null
                ]);
                
                $this->logFileActivity($fileModel->id, 'upload', $user->id, [
                    'original_name' => $originalName,
                    'file_size' => $file->getSize()
                ]);
                
                $uploaded++;
            } catch (\Exception $e) {
                $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
            }
        }
        
        return response()->json([
            'success' => $uploaded > 0,
            'message' => "Successfully uploaded {$uploaded} file(s)",
            'uploaded_count' => $uploaded,
            'errors' => $errors
        ]);
    }
    
    /**
     * Handle Create Nested Folder
     */
    private function handleCreateNestedFolder(Request $request)
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
                'message' => 'You do not have permission to create folders.'
            ], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:file_folders,id',
            'description' => 'nullable|string',
            'access_level' => 'required|in:public,department,private',
            'department_id' => 'nullable|integer|exists:departments,id'
        ]);
        
        // Check if folder name already exists in parent
        $existing = FileFolder::where('parent_id', $request->parent_id)
            ->where('name', $request->name)
            ->exists();
        
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'A folder with this name already exists in this location.'
            ]);
        }
        
        $folder = FileFolder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'access_level' => $request->access_level,
            'department_id' => $request->department_id,
            'created_by' => $user->id,
            'path' => $this->generateFolderPath($request->parent_id)
        ]);
        
        $this->logFileActivity(null, 'folder_created', $user->id, [
            'folder_id' => $folder->id,
            'folder_name' => $folder->name,
            'parent_id' => $request->parent_id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Folder created successfully',
            'folder' => $folder->load(['creator', 'department', 'parent'])
        ]);
    }
    
    /**
     * Handle Bulk Create Folders from Excel
     */
    private function handleBulkCreateFoldersExcel(Request $request)
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
                'message' => 'You do not have permission to create folders.'
            ], 403);
        }
        
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ]);
        
        try {
            $file = $request->file('excel_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();
            $rows = [];
            
            // Read file based on extension
            if ($extension === 'csv' || $mimeType === 'text/csv' || strpos($mimeType, 'csv') !== false) {
                // Read CSV file natively with better error handling
                $filePath = $file->getRealPath();
                $handle = @fopen($filePath, 'r');
                
                if ($handle === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not read CSV file. Please check file permissions.'
                    ]);
                }
                
                // Skip BOM if present (UTF-8 BOM: EF BB BF)
                $bom = fread($handle, 3);
                if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                    rewind($handle);
                }
                
                $lineNumber = 0;
                while (($row = fgetcsv($handle, 0, ',')) !== false) {
                    $lineNumber++;
                    // Skip empty rows
                    if (empty(array_filter($row, function($cell) { return trim($cell) !== ''; }))) {
                        continue;
                    }
                    $rows[] = $row;
                }
                fclose($handle);
                
                if (empty($rows)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'CSV file is empty or contains no valid data.'
                    ]);
                }
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                // Try to use Laravel Excel if available
                if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
                    try {
                        $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
                        if (!empty($data) && !empty($data[0])) {
                            $rows = $data[0];
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Excel file appears to be empty or invalid.'
                            ]);
                        }
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Error reading Excel file: ' . $e->getMessage() . '. Please try converting to CSV format.'
                        ]);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Excel file (.xlsx/.xls) support requires the Laravel Excel package. Please either: (1) Install it with: composer require maatwebsite/excel, or (2) Convert your Excel file to CSV format and upload the CSV file instead.'
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported file format. Please use CSV (.csv), Excel (.xlsx), or Excel 97-2003 (.xls) files. CSV format is recommended for best compatibility.'
                ]);
            }
            
            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is empty or contains no valid data rows.'
                ]);
            }
            
            // Remove header row if it exists
            $header = array_shift($rows);
            
            // Validate header row format (optional check)
            if (!empty($header) && count($header) < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file format. Expected at least one column (Folder Name).'
                ]);
            }
            
            // Sort rows to process parent folders before child folders
            // Folders with parent paths should be processed after folders without parents
            usort($rows, function($a, $b) {
                $aParent = !empty($a[1]) ? trim($a[1]) : '';
                $bParent = !empty($b[1]) ? trim($b[1]) : '';
                
                // Folders without parents come first
                if (empty($aParent) && !empty($bParent)) return -1;
                if (!empty($aParent) && empty($bParent)) return 1;
                
                // If both have parents, sort by path depth (shorter paths first)
                $aDepth = substr_count($aParent, '/');
                $bDepth = substr_count($bParent, '/');
                if ($aDepth !== $bDepth) {
                    return $aDepth - $bDepth;
                }
                
                return 0;
            });
            
            $created = 0;
            $errors = [];
            $createdFolders = []; // Track created folders for nested path resolution
            
            foreach ($rows as $index => $row) {
                try {
                    // Expected columns: name, parent_folder (optional), description, access_level, department
                    $folderData = [
                        'name' => $row[0] ?? null,
                        'parent_folder_name' => $row[1] ?? null,
                        'description' => $row[2] ?? null,
                        'access_level' => $row[3] ?? 'public',
                        'department_name' => $row[4] ?? null,
                    ];
                    
                    if (empty($folderData['name'])) {
                        $errors[] = "Row " . ($index + 2) . ": Folder name is required";
                        continue;
                    }
                    
                    // Find parent folder if specified - support nested paths like "Parent/Subfolder"
                    $parentId = null;
                    if (!empty($folderData['parent_folder_name'])) {
                        $parentPath = trim($folderData['parent_folder_name']);
                        
                        // Check if it's a nested path (contains /)
                        if (strpos($parentPath, '/') !== false) {
                            $pathParts = explode('/', $parentPath);
                            $currentParent = null;
                            
                            // Navigate through the path
                            foreach ($pathParts as $part) {
                                $part = trim($part);
                                if (empty($part)) continue;
                                
                                $folder = FileFolder::where('name', $part)
                                    ->where('parent_id', $currentParent)
                                    ->first();
                                
                                if (!$folder) {
                                    $errors[] = "Row " . ($index + 2) . ": Parent folder path '{$parentPath}' not found (failed at '{$part}')";
                                    $currentParent = null;
                                    break;
                                }
                                
                                $currentParent = $folder->id;
                            }
                            
                            $parentId = $currentParent;
                        } else {
                            // Simple folder name lookup
                            $parent = FileFolder::where('name', $parentPath)->first();
                            if ($parent) {
                                $parentId = $parent->id;
                            } else {
                                $errors[] = "Row " . ($index + 2) . ": Parent folder '{$parentPath}' not found";
                                continue;
                            }
                        }
                    }
                    
                    // Find department if specified
                    $departmentId = null;
                    if (!empty($folderData['department_name'])) {
                        $department = Department::where('name', $folderData['department_name'])->first();
                        if ($department) {
                            $departmentId = $department->id;
                        }
                    }
                    
                    // Check if folder already exists
                    $existing = FileFolder::where('parent_id', $parentId)
                        ->where('name', $folderData['name'])
                        ->exists();
                    
                    if ($existing) {
                        $errors[] = "Row " . ($index + 2) . ": Folder '{$folderData['name']}' already exists";
                        continue;
                    }
                    
                    // Create folder
                    $folder = FileFolder::create([
                        'name' => $folderData['name'],
                        'parent_id' => $parentId,
                        'description' => $folderData['description'],
                        'access_level' => $folderData['access_level'],
                        'department_id' => $departmentId,
                        'created_by' => $user->id,
                        'path' => $this->generateFolderPath($parentId)
                    ]);
                    
                    $created++;
                    
                    // Store created folder for nested path resolution
                    $folderPath = $parentId ? 
                        ($createdFolders[$parentId] ?? '') . '/' . $folderData['name'] : 
                        $folderData['name'];
                    $createdFolders[$folder->id] = $folderPath;
                    
                    $this->logFileActivity(null, 'folder_created', $user->id, [
                        'folder_id' => $folder->id,
                        'folder_name' => $folder->name,
                        'parent_id' => $parentId,
                        'source' => 'excel_import'
                    ]);
                    
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => $created > 0,
                'message' => "Successfully created {$created} folder(s) from Excel",
                'created_count' => $created,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing Excel file: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Download Excel Template for Bulk Folder Creation
     */
    public function downloadFolderTemplate()
    {
        $filename = 'folder_bulk_import_template_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $data = [
            ['Folder Name', 'Parent Folder Path', 'Description', 'Access Level', 'Department Name'],
            ['HR Documents', '', 'Human Resources documents', 'public', 'HR'],
            ['2024 Reports', 'HR Documents', 'Reports for year 2024', 'department', 'HR'],
            ['Monthly Reports', 'HR Documents/2024 Reports', 'Monthly report files', 'private', 'HR'],
            ['Finance', '', 'Finance department files', 'public', 'Finance'],
            ['Budget 2024', 'Finance', 'Budget documents for 2024', 'department', 'Finance'],
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
     * Handle Update File
     */
    private function handleUpdateFile(Request $request)
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
                'message' => 'You do not have permission to edit files.'
            ], 403);
        }
        
        $request->validate([
            'file_id' => 'required|integer|exists:files,id',
            'description' => 'nullable|string|max:1000',
            'access_level' => 'nullable|in:public,department,private',
            'confidential_level' => 'nullable|in:normal,confidential,strictly_confidential',
            'tags' => 'nullable|string|max:255'
        ]);
        
        $file = FileModel::findOrFail($request->file_id);
        
        $file->update([
            'description' => $request->description,
            'access_level' => $request->access_level ?? $file->access_level,
            'confidential_level' => $request->confidential_level ?? $file->confidential_level,
            'tags' => $request->tags
        ]);
        
        $this->logFileActivity($file->id, 'file_updated', $user->id, [
            'original_name' => $file->original_name,
            'changes' => $request->only(['description', 'access_level', 'confidential_level', 'tags'])
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'File updated successfully',
            'file' => $file->fresh()
        ]);
    }
    
    /**
     * Handle Get File Details
     */
    private function handleGetFileDetails(Request $request)
    {
        $user = Auth::user();
        $fileId = $request->input('file_id');
        
        $file = FileModel::with(['uploader', 'folder', 'assignments.user'])
            ->findOrFail($fileId);
        
        // Check access
        if (!$this->hasFileAccess($file, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this file.'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'file' => [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'description' => $file->description,
                'access_level' => $file->access_level,
                'confidential_level' => $file->confidential_level,
                'tags' => $file->tags,
                'file_size' => $this->formatFileSize($file->file_size),
                'mime_type' => $file->mime_type,
                'uploaded_by' => $file->uploader->name ?? 'System',
                'folder_name' => $file->folder->name ?? 'N/A',
                'created_at' => $file->created_at->format('M d, Y H:i')
            ]
        ]);
    }
    
}

