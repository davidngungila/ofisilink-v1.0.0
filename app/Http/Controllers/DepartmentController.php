<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Only HR and System Admins can manage departments');
        }

        $departments = Department::with(['head', 'primaryUsers'])
            ->orderBy('name')
            ->get();

        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modules.hr.departments', compact('departments', 'users'));
    }

    public function show(Department $department)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            abort(403, 'Unauthorized');
        }

        $department->load(['head', 'primaryUsers']);

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'department' => $department
            ]);
        }

        // Return view for browser requests
        return view('modules.hr.department-show', compact('department'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'nullable|string|max:50|unique:departments,code',
            'description' => 'nullable|string|max:1000',
            'head_id' => 'nullable|exists:users,id',
        ]);

        $department = Department::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'head_id' => $request->head_id,
            'is_active' => true,
        ]);

        // Log activity
        ActivityLogService::logCreated($department, "Created department: {$department->name}", [
            'name' => $department->name,
            'code' => $department->code,
            'head_id' => $department->head_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully!'
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'nullable|string|max:50|unique:departments,code,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'head_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $oldValues = $department->toArray();
        $department->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'head_id' => $request->head_id,
            'is_active' => $request->has('is_active') ? $request->is_active : $department->is_active,
        ]);

        // Log activity
        $oldValuesFiltered = array_intersect_key($oldValues, $department->getChanges());
        ActivityLogService::logUpdated($department, $oldValuesFiltered, $department->getChanges(), "Updated department: {$department->name}", [
            'name' => $department->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully!'
        ]);
    }

    public function destroy(Department $department)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if department has users
        if ($department->primaryUsers()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with assigned users'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully!'
        ]);
    }
}

