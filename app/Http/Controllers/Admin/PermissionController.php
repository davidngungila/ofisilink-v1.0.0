<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        // Extra safety: ensure only System Admin can access controller actions
        $this->middleware('role:System Admin');
    }

    public function index(Request $request)
    {
        $query = Permission::with('roles');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('display_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('module', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by module
        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'module');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSortFields = ['name', 'display_name', 'module', 'created_at', 'is_active'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('module')->orderBy('display_name');
        }

        $perPage = $request->get('per_page', 50);
        $permissions = $query->paginate($perPage)->withQueryString();
        $modules = Permission::distinct()->pluck('module')->sort()->values();
        $roles = Role::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => Permission::count(),
            'active' => Permission::where('is_active', true)->count(),
            'inactive' => Permission::where('is_active', false)->count(),
            'modules_count' => Permission::distinct('module')->count(),
            'by_module' => Permission::select('module', DB::raw('count(*) as count'))
                ->groupBy('module')
                ->orderBy('count', 'desc')
                ->get(),
            'by_role' => DB::table('role_permissions')
                ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
                ->select('roles.name', 'roles.display_name', DB::raw('count(*) as count'))
                ->groupBy('roles.id', 'roles.name', 'roles.display_name')
                ->orderBy('count', 'desc')
                ->get(),
            'unassigned' => Permission::whereDoesntHave('roles')->count(),
        ];

        return view('admin.permissions.index', compact('permissions', 'modules', 'roles', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'module' => 'required|string',
        ]);

        $permission = Permission::create($request->all());

        // Log activity
        ActivityLogService::logCreated($permission, "Created permission: {$permission->name}", [
            'name' => $permission->name,
            'display_name' => $permission->display_name,
            'module' => $permission->module,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully',
            'permission' => $permission
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'module' => 'required|string',
        ]);

        $oldValues = array_intersect_key($permission->toArray(), $permission->getChanges());
        $permission->update($request->all());

        // Log activity
        ActivityLogService::logUpdated($permission, $oldValues, $permission->getChanges(), "Updated permission: {$permission->name}", [
            'name' => $permission->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully',
            'permission' => $permission
        ]);
    }

    public function destroy(Permission $permission)
    {
        $permissionData = $permission->toArray();
        $permission->delete();

        // Log activity
        ActivityLogService::logDeleted($permission, "Deleted permission: {$permissionData['name']}", [
            'name' => $permissionData['name'],
            'display_name' => $permissionData['display_name'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully'
        ]);
    }

    public function toggleStatus(Permission $permission)
    {
        $oldStatus = $permission->is_active;
        $permission->update(['is_active' => !$permission->is_active]);

        // Log activity
        ActivityLogService::logAction('permission_status_toggled', "Toggled permission {$permission->name} status from " . ($oldStatus ? 'active' : 'inactive') . " to " . ($permission->is_active ? 'active' : 'inactive'), $permission, [
            'name' => $permission->name,
            'old_status' => $oldStatus,
            'new_status' => $permission->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission status updated successfully',
            'is_active' => $permission->is_active
        ]);
    }
    
    public function assignToRoles(Request $request, Permission $permission)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);
        
        $oldRoles = $permission->roles->pluck('id')->toArray();
        $permission->roles()->sync($request->roles);
        
        // Log activity
        ActivityLogService::logAction('permission_roles_assigned', "Assigned permission {$permission->name} to roles", $permission, [
            'name' => $permission->name,
            'old_roles' => $oldRoles,
            'new_roles' => $request->roles,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission assigned to roles successfully',
            'permission' => $permission->load('roles')
        ]);
    }
    
    public function getPermissionRoles(Permission $permission)
    {
        return response()->json([
            'success' => true,
            'roles' => $permission->roles->pluck('id')->toArray()
        ]);
    }

    public function show(Permission $permission)
    {
        $permission->load('roles');
        
        // Return JSON if AJAX request
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'permission' => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'description' => $permission->description,
                    'module' => $permission->module,
                    'is_active' => $permission->is_active,
                    'roles' => $permission->roles->map(function($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'display_name' => $role->display_name,
                        ];
                    }),
                    'users_count' => $permission->users()->count(),
                    'created_at' => $permission->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $permission->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);
        }
        
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Bulk activate permissions
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        try {
            $count = Permission::whereIn('id', $request->permission_ids)
                ->where('is_active', false)
                ->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => "Successfully activated {$count} permission(s).",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk deactivate permissions
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        try {
            $count = Permission::whereIn('id', $request->permission_ids)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => "Successfully deactivated {$count} permission(s).",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete permissions
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        try {
            $permissions = Permission::whereIn('id', $request->permission_ids)->get();
            $count = $permissions->count();
            
            foreach ($permissions as $permission) {
                $permission->roles()->detach();
                $permission->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} permission(s).",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export permissions to CSV
     */
    public function export(Request $request)
    {
        $query = Permission::with('roles');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('display_name', 'like', '%' . $request->search . '%')
                  ->orWhere('module', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        $permissions = $query->orderBy('module')->orderBy('display_name')->get();

        $filename = 'permissions_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($permissions) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Name', 'Display Name', 'Module', 'Description', 
                'Roles', 'Status', 'Users Count', 'Created At'
            ]);

            // Data
            foreach ($permissions as $permission) {
                fputcsv($file, [
                    $permission->id,
                    $permission->name,
                    $permission->display_name,
                    $permission->module,
                    $permission->description ?? 'N/A',
                    $permission->roles->pluck('display_name')->join(', ') ?: 'No roles',
                    $permission->is_active ? 'Active' : 'Inactive',
                    $permission->users()->count(),
                    $permission->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get permission statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => Permission::count(),
            'active' => Permission::where('is_active', true)->count(),
            'inactive' => Permission::where('is_active', false)->count(),
            'modules_count' => Permission::distinct('module')->count(),
            'by_module' => Permission::select('module', DB::raw('count(*) as count'))
                ->groupBy('module')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'module' => $item->module,
                        'count' => $item->count
                    ];
                }),
            'by_role' => DB::table('role_permissions')
                ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
                ->select('roles.display_name', DB::raw('count(*) as count'))
                ->groupBy('roles.id', 'roles.display_name')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'role' => $item->display_name,
                        'count' => $item->count
                    ];
                }),
            'unassigned' => Permission::whereDoesntHave('roles')->count(),
        ];

        return response()->json($stats);
    }
}
