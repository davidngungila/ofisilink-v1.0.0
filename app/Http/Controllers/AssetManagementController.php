<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AssetCategory;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetIssue;
use App\Models\AssetMaintenance;

class AssetManagementController extends Controller
{
    public function index()
    {
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $users = \App\Models\User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('modules.assets.index', compact('departments', 'users'));
    }

    // Categories
    public function listCategories()
    {
        $categories = AssetCategory::orderBy('name')->get();
        return response()->json(['success'=>true,'items'=>$categories]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'description'=>'nullable|string',
            'code'=>'required|string|max:50|unique:asset_categories,code',
            'depreciation_years'=>'required|integer|min:1|max:50',
            'depreciation_rate'=>'required|numeric|min:0|max:100',
            'is_active'=>'boolean'
        ]);
        $cat = AssetCategory::create($data);
        return response()->json(['success'=>true,'item'=>$cat]);
    }

    public function updateCategory(Request $request, AssetCategory $category)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'description'=>'nullable|string',
            'code'=>'required|string|max:50|unique:asset_categories,code,' . $category->id,
            'depreciation_years'=>'required|integer|min:1|max:50',
            'depreciation_rate'=>'required|numeric|min:0|max:100',
            'is_active'=>'boolean'
        ]);
        $category->update($data);
        return response()->json(['success'=>true]);
    }

    public function destroyCategory(AssetCategory $category)
    {
        if ($category->assets()->exists()) {
            return response()->json(['success'=>false,'message'=>'Cannot delete category with assets.'], 422);
        }
        $category->delete();
        return response()->json(['success'=>true]);
    }

    // Assets
    public function listAssets(Request $request)
    {
        try {
            $query = Asset::with(['category', 'department', 'assignedUser', 'creator']);
            
            // Apply filters
            if($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if($request->has('status')) {
                $query->where('status', $request->status);
            }
            if($request->has('condition')) {
                $query->where('condition', $request->condition);
            }
            if($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('asset_tag', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $assets = $query->orderBy('created_at','desc')->paginate(20);
            
            // Map assigned_user from latest active assignment
            $assets->getCollection()->transform(function($asset) {
                $latest = $asset->assignments()->where('status','active')->latest('assigned_date')->first();
                $asset->assigned_user = $latest ? $latest->user : null;
                $asset->current_value = $asset->calculateDepreciation();
                return $asset;
            });
            
            return response()->json(['success'=>true,'items'=>$assets]);
        } catch (\Throwable $e) {
            \Log::error('Assets list error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Server error while loading assets.'], 500);
        }
    }

    public function getAsset(Asset $asset)
    {
        $asset->load(['category', 'department', 'assignedUser', 'creator', 'assignments.user', 'issues.reportedBy', 'maintenance.assignedTo']);
        $asset->current_value = $asset->calculateDepreciation();
        return response()->json(['success'=>true,'item'=>$asset]);
    }

    public function storeAsset(Request $request)
    {
        $data = $request->validate([
            'category_id'=>'required|exists:asset_categories,id',
            'asset_tag'=>'required|string|max:100|unique:assets,asset_tag',
            'name'=>'required|string|max:255',
            'description'=>'nullable|string',
            'brand'=>'nullable|string|max:100',
            'model'=>'nullable|string|max:100',
            'serial_number'=>'nullable|string|max:150|unique:assets,serial_number',
            'location'=>'nullable|string|max:255',
            'department_id'=>'nullable|exists:departments,id',
            'status'=>'required|in:available,assigned,maintenance,disposed,lost',
            'condition'=>'required|in:excellent,good,fair,poor,damaged',
            'purchase_date'=>'nullable|date',
            'purchase_price'=>'nullable|numeric',
            'supplier'=>'nullable|string|max:255',
            'warranty_period'=>'nullable|string|max:50',
            'warranty_expiry'=>'nullable|date',
            'notes'=>'nullable|string'
        ]);
        $data['created_by'] = Auth::id();
        $asset = Asset::create($data);
        return response()->json(['success'=>true,'item'=>$asset]);
    }

    public function updateAsset(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'category_id'=>'required|exists:asset_categories,id',
            'asset_tag'=>'required|string|max:100|unique:assets,asset_tag,' . $asset->id,
            'name'=>'required|string|max:255',
            'description'=>'nullable|string',
            'brand'=>'nullable|string|max:100',
            'model'=>'nullable|string|max:100',
            'serial_number'=>'nullable|string|max:150|unique:assets,serial_number,' . $asset->id,
            'location'=>'nullable|string|max:255',
            'department_id'=>'nullable|exists:departments,id',
            'status'=>'required|in:available,assigned,maintenance,disposed,lost',
            'condition'=>'required|in:excellent,good,fair,poor,damaged',
            'purchase_date'=>'nullable|date',
            'purchase_price'=>'nullable|numeric',
            'supplier'=>'nullable|string|max:255',
            'warranty_period'=>'nullable|string|max:50',
            'warranty_expiry'=>'nullable|date',
            'notes'=>'nullable|string'
        ]);
        $asset->update($data);
        return response()->json(['success'=>true]);
    }

    public function destroyAsset(Asset $asset)
    {
        if ($asset->assignments()->where('status','active')->exists()) {
            return response()->json(['success'=>false,'message'=>'Cannot delete asset with active assignment.'], 422);
        }
        $asset->delete();
        return response()->json(['success'=>true]);
    }

    // Assignments
    public function assignAsset(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'assigned_to'=>'required|exists:users,id',
            'assigned_date'=>'required|date',
            'notes'=>'nullable|string'
        ]);
        
        try {
            DB::transaction(function() use ($asset, $data){
                // Close any existing active assignments
                $asset->assignments()->where('status','active')->update([
                    'status'=>'returned',
                    'return_date'=>now(),
                    'returned_to'=>Auth::id()
                ]);
                
                // Create new assignment
                $assignment = $asset->assignments()->create([
                    'assigned_to'=>$data['assigned_to'],
                    'assigned_by'=>Auth::id(),
                    'assigned_date'=>$data['assigned_date'],
                    'status'=>'active',
                    'notes'=>$data['notes'] ?? null,
                ]);
                
                // Update asset status
                $asset->update([
                    'status'=>'assigned',
                    'assigned_to'=>$data['assigned_to']
                ]);
            });
            return response()->json(['success'=>true,'message'=>'Asset assigned successfully']);
        } catch (\Exception $e) {
            \Log::error('Asset assignment error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to assign asset: '.$e->getMessage()], 500);
        }
    }

    public function returnAsset(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'return_date'=>'required|date',
            'condition'=>'nullable|in:excellent,good,fair,poor,damaged',
            'notes'=>'nullable|string'
        ]);
        
        try {
            DB::transaction(function() use ($asset, $data){
                $assignment = $asset->assignments()->where('status','active')->latest('assigned_date')->first();
                if(!$assignment){ 
                    throw new \Exception('No active assignment found.');
                }
                
                $assignment->update([
                    'status'=>'returned',
                    'return_date'=>$data['return_date'],
                    'returned_to'=>Auth::id(),
                    'notes'=>$data['notes'] ?? $assignment->notes
                ]);
                
                $updateData = ['status'=>'available','assigned_to'=>null];
                if(isset($data['condition'])) {
                    $updateData['condition'] = $data['condition'];
                }
                $asset->update($updateData);
            });
            return response()->json(['success'=>true,'message'=>'Asset returned successfully']);
        } catch (\Exception $e) {
            \Log::error('Asset return error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>$e->getMessage()], 422);
        }
    }

    public function listAssignments(Request $request)
    {
        try {
            $query = AssetAssignment::with(['asset.category', 'user', 'assignedBy'])
                ->orderBy('created_at', 'desc');
            
            if($request->has('asset_id')) {
                $query->where('asset_id', $request->asset_id);
            }
            if($request->has('user_id')) {
                $query->where('assigned_to', $request->user_id);
            }
            if($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            $assignments = $query->paginate(20);
            return response()->json(['success'=>true,'items'=>$assignments]);
        } catch (\Exception $e) {
            \Log::error('List assignments error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to load assignments'], 500);
        }
    }

    // Issues
    public function reportIssue(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'issue_type'=>'required|in:maintenance,damage,loss,theft,other',
            'priority'=>'required|in:low,medium,high,urgent',
            'description'=>'required|string',
            'assigned_to'=>'nullable|exists:users,id'
        ]);
        
        try {
            DB::transaction(function() use ($asset, $data){
                $issue = $asset->issues()->create([
                    'reported_by'=>Auth::id(),
                    'assigned_to'=>$data['assigned_to'] ?? null,
                    'status'=>'reported',
                    'reported_date'=>now(),
                    'title'=>$data['title'],
                    'issue_type'=>$data['issue_type'],
                    'priority'=>$data['priority'],
                    'description'=>$data['description']
                ]);
                
                // Update asset status if issue is critical
                if(in_array($data['priority'], ['high', 'urgent']) || $data['issue_type'] === 'damage') {
                    $asset->update(['status'=>'maintenance']);
                }
            });
            
            return response()->json(['success'=>true,'message'=>'Issue reported successfully']);
        } catch (\Exception $e) {
            \Log::error('Report issue error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to report issue'], 500);
        }
    }

    public function updateIssue(Request $request, AssetIssue $issue)
    {
        $data = $request->validate([
            'status'=>'required|in:reported,in_progress,resolved,closed,cancelled',
            'assigned_to'=>'nullable|exists:users,id',
            'resolution_notes'=>'nullable|string',
            'cost'=>'nullable|numeric'
        ]);
        
        try {
            DB::transaction(function() use ($issue, $data){
                $updateData = $data;
                
                // Set resolved_date when status changes to resolved or closed
                if(in_array($data['status'], ['resolved', 'closed']) && !$issue->resolved_date) {
                    $updateData['resolved_date'] = now();
                }
                
                $issue->update($updateData);
                
                // Update asset status if issue is resolved
                if(in_array($data['status'], ['resolved', 'closed']) && $issue->asset->status === 'maintenance') {
                    // Check if there are other active issues
                    $hasActiveIssues = $issue->asset->issues()
                        ->where('id', '!=', $issue->id)
                        ->whereIn('status', ['reported', 'in_progress'])
                        ->exists();
                    
                    if(!$hasActiveIssues) {
                        $issue->asset->update(['status'=>'available']);
                    }
                }
            });
            
            return response()->json(['success'=>true,'message'=>'Issue updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Update issue error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to update issue'], 500);
        }
    }

    public function listIssues(Request $request)
    {
        try {
            $query = AssetIssue::with(['asset.category', 'reportedBy', 'assignedTo'])
                ->orderBy('created_at', 'desc');
            
            if($request->has('asset_id')) {
                $query->where('asset_id', $request->asset_id);
            }
            if($request->has('status')) {
                $query->where('status', $request->status);
            }
            if($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            if($request->has('issue_type')) {
                $query->where('issue_type', $request->issue_type);
            }
            if($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('asset', function($assetQuery) use ($search) {
                          $assetQuery->where('asset_tag', 'like', "%{$search}%")
                                     ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            }
            
            $issues = $query->paginate(20);
            return response()->json(['success'=>true,'items'=>$issues]);
        } catch (\Exception $e) {
            \Log::error('List issues error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to load issues'], 500);
        }
    }

    public function getIssue(AssetIssue $issue)
    {
        $issue->load(['asset.category', 'reportedBy', 'assignedTo']);
        return response()->json(['success'=>true,'item'=>$issue]);
    }

    // Maintenance
    public function scheduleMaintenance(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'maintenance_type'=>'required|in:preventive,corrective,inspection,upgrade',
            'title'=>'required|string|max:255',
            'description'=>'nullable|string',
            'scheduled_date'=>'required|date',
            'assigned_to'=>'nullable|exists:users,id',
            'vendor_name'=>'nullable|string|max:255',
            'cost'=>'nullable|numeric'
        ]);
        
        try {
            DB::transaction(function() use ($asset, $data){
                $maintenance = $asset->maintenance()->create([
                    'maintenance_type'=>$data['maintenance_type'],
                    'title'=>$data['title'],
                    'description'=>$data['description'] ?? null,
                    'scheduled_date'=>$data['scheduled_date'],
                    'assigned_to'=>$data['assigned_to'] ?? null,
                    'vendor_name'=>$data['vendor_name'] ?? null,
                    'cost'=>$data['cost'] ?? null,
                    'status'=>'scheduled'
                ]);
                
                // Update asset status if not already in maintenance
                if($asset->status !== 'maintenance') {
                    $asset->update(['status'=>'maintenance']);
                }
            });
            
            return response()->json(['success'=>true,'message'=>'Maintenance scheduled successfully']);
        } catch (\Exception $e) {
            \Log::error('Schedule maintenance error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to schedule maintenance'], 500);
        }
    }

    public function updateMaintenance(Request $request, AssetMaintenance $maintenance)
    {
        $data = $request->validate([
            'status'=>'required|in:scheduled,in_progress,completed,cancelled',
            'completed_date'=>'nullable|date',
            'cost'=>'nullable|numeric',
            'vendor_name'=>'nullable|string|max:255',
            'notes'=>'nullable|string'
        ]);
        
        try {
            DB::transaction(function() use ($maintenance, $data){
                $updateData = $data;
                
                // Set completed_date when status changes to completed
                if($data['status'] === 'completed' && !$maintenance->completed_date) {
                    $updateData['completed_date'] = $data['completed_date'] ?? now();
                }
                
                $maintenance->update($updateData);
                
                // Update asset status when maintenance is completed or cancelled
                if(in_array($data['status'], ['completed', 'cancelled'])) {
                    // Check if there are other active maintenance records
                    $hasActiveMaintenance = $maintenance->asset->maintenance()
                        ->where('id', '!=', $maintenance->id)
                        ->whereIn('status', ['scheduled', 'in_progress'])
                        ->exists();
                    
                    // Check if there are active issues
                    $hasActiveIssues = $maintenance->asset->issues()
                        ->whereIn('status', ['reported', 'in_progress'])
                        ->exists();
                    
                    if(!$hasActiveMaintenance && !$hasActiveIssues) {
                        $maintenance->asset->update(['status'=>'available']);
                    }
                } elseif($data['status'] === 'in_progress') {
                    $maintenance->asset->update(['status'=>'maintenance']);
                }
            });
            
            return response()->json(['success'=>true,'message'=>'Maintenance updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Update maintenance error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to update maintenance'], 500);
        }
    }

    public function listMaintenance(Request $request)
    {
        try {
            $query = AssetMaintenance::with(['asset.category', 'assignedTo'])
                ->orderBy('created_at', 'desc');
            
            if($request->has('asset_id')) {
                $query->where('asset_id', $request->asset_id);
            }
            if($request->has('status')) {
                $query->where('status', $request->status);
            }
            if($request->has('maintenance_type')) {
                $query->where('maintenance_type', $request->maintenance_type);
            }
            
            $maintenance = $query->paginate(20);
            return response()->json(['success'=>true,'items'=>$maintenance]);
        } catch (\Exception $e) {
            \Log::error('List maintenance error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to load maintenance records'], 500);
        }
    }

    public function getMaintenance(AssetMaintenance $maintenance)
    {
        $maintenance->load(['asset.category', 'assignedTo']);
        return response()->json(['success'=>true,'item'=>$maintenance]);
    }

    // Advanced Issue Management Features
    public function getIssueStatistics()
    {
        try {
            $stats = [
                'total' => AssetIssue::count(),
                'reported' => AssetIssue::where('status', 'reported')->count(),
                'in_progress' => AssetIssue::where('status', 'in_progress')->count(),
                'resolved' => AssetIssue::where('status', 'resolved')->count(),
                'closed' => AssetIssue::where('status', 'closed')->count(),
                'cancelled' => AssetIssue::where('status', 'cancelled')->count(),
                'by_priority' => [
                    'low' => AssetIssue::where('priority', 'low')->count(),
                    'medium' => AssetIssue::where('priority', 'medium')->count(),
                    'high' => AssetIssue::where('priority', 'high')->count(),
                    'urgent' => AssetIssue::where('priority', 'urgent')->count(),
                ],
                'by_type' => [
                    'maintenance' => AssetIssue::where('issue_type', 'maintenance')->count(),
                    'damage' => AssetIssue::where('issue_type', 'damage')->count(),
                    'loss' => AssetIssue::where('issue_type', 'loss')->count(),
                    'theft' => AssetIssue::where('issue_type', 'theft')->count(),
                    'other' => AssetIssue::where('issue_type', 'other')->count(),
                ],
                'avg_resolution_time' => AssetIssue::whereNotNull('resolved_date')
                    ->whereNotNull('reported_date')
                    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, reported_date, resolved_date)) as avg_hours')
                    ->value('avg_hours'),
            ];

            return response()->json(['success'=>true,'stats'=>$stats]);
        } catch (\Exception $e) {
            \Log::error('Get issue statistics error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to load statistics'], 500);
        }
    }

    public function bulkUpdateIssues(Request $request)
    {
        $data = $request->validate([
            'issue_ids' => 'required|array',
            'issue_ids.*' => 'exists:asset_issues,id',
            'action' => 'required|in:assign,update_status,update_priority,delete',
            'status' => 'required_if:action,update_status|in:reported,in_progress,resolved,closed,cancelled',
            'priority' => 'required_if:action,update_priority|in:low,medium,high,urgent',
            'assigned_to' => 'required_if:action,assign|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::transaction(function() use ($data) {
                $issues = AssetIssue::whereIn('id', $data['issue_ids'])->get();
                
                foreach ($issues as $issue) {
                    if ($data['action'] === 'assign') {
                        $issue->update(['assigned_to' => $data['assigned_to']]);
                    } elseif ($data['action'] === 'update_status') {
                        $updateData = ['status' => $data['status']];
                        if (in_array($data['status'], ['resolved', 'closed']) && !$issue->resolved_date) {
                            $updateData['resolved_date'] = now();
                        }
                        $issue->update($updateData);
                        
                        // Update asset status if all issues resolved
                        if (in_array($data['status'], ['resolved', 'closed'])) {
                            $hasActiveIssues = $issue->asset->issues()
                                ->where('id', '!=', $issue->id)
                                ->whereIn('status', ['reported', 'in_progress'])
                                ->exists();
                            
                            if (!$hasActiveIssues && $issue->asset->status === 'maintenance') {
                                $issue->asset->update(['status' => 'available']);
                            }
                        }
                    } elseif ($data['action'] === 'update_priority') {
                        $issue->update(['priority' => $data['priority']]);
                    } elseif ($data['action'] === 'delete') {
                        $issue->delete();
                    }
                }
            });

            return response()->json(['success'=>true,'message'=>'Bulk operation completed successfully']);
        } catch (\Exception $e) {
            \Log::error('Bulk update issues error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to perform bulk operation'], 500);
        }
    }

    public function exportIssues(Request $request)
    {
        try {
            $query = AssetIssue::with(['asset.category', 'reportedBy', 'assignedTo'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            if ($request->has('issue_type')) {
                $query->where('issue_type', $request->issue_type);
            }
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $issues = $query->get();

            $csv = "Issue ID,Asset Tag,Asset Name,Category,Title,Type,Priority,Status,Reported By,Assigned To,Reported Date,Resolved Date,Cost,Description\n";
            
            foreach ($issues as $issue) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,\"%s\"\n",
                    $issue->id,
                    $issue->asset->asset_tag ?? '',
                    $issue->asset->name ?? '',
                    $issue->asset->category->name ?? '',
                    $issue->title,
                    $issue->issue_type,
                    $issue->priority,
                    $issue->status,
                    $issue->reportedBy->name ?? '',
                    $issue->assignedTo->name ?? '',
                    $issue->reported_date ? $issue->reported_date->format('Y-m-d') : '',
                    $issue->resolved_date ? $issue->resolved_date->format('Y-m-d') : '',
                    $issue->cost ?? '',
                    str_replace('"', '""', $issue->description)
                );
            }

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename=asset_issues_'.date('Y-m-d').'.csv');
        } catch (\Exception $e) {
            \Log::error('Export issues error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to export issues'], 500);
        }
    }

    public function getIssueHistory(AssetIssue $issue)
    {
        try {
            // Get issue history from activity logs or create a timeline
            $history = [
                [
                    'date' => $issue->created_at,
                    'action' => 'Issue Reported',
                    'user' => $issue->reportedBy->name ?? 'System',
                    'notes' => 'Issue reported: ' . $issue->title
                ]
            ];

            if ($issue->assigned_to && $issue->assignedTo) {
                $history[] = [
                    'date' => $issue->updated_at,
                    'action' => 'Assigned',
                    'user' => 'System',
                    'notes' => 'Assigned to: ' . $issue->assignedTo->name
                ];
            }

            if ($issue->status === 'in_progress') {
                $history[] = [
                    'date' => $issue->updated_at,
                    'action' => 'In Progress',
                    'user' => 'System',
                    'notes' => 'Issue marked as in progress'
                ];
            }

            if ($issue->resolved_date) {
                $history[] = [
                    'date' => $issue->resolved_date,
                    'action' => 'Resolved',
                    'user' => 'System',
                    'notes' => $issue->resolution_notes ?? 'Issue resolved'
                ];
            }

            return response()->json(['success'=>true,'history'=>$history]);
        } catch (\Exception $e) {
            \Log::error('Get issue history error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to load history'], 500);
        }
    }
}


