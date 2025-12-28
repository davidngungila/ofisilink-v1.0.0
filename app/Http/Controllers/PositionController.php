<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use App\Models\Employee;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Only HR and System Admins can manage positions');
        }

        $positions = Position::with(['department'])
            ->orderBy('title')
            ->get();

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modules.hr.positions', compact('positions', 'departments'));
    }

    public function show(Position $position)
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

        $position->load('department');
        
        // Load employees separately since position is stored as string (not foreign key)
        $position->employees_count = Employee::where('position', $position->title)->count();
        $position->employees_list = Employee::where('position', $position->title)
            ->with('user')
            ->get();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'position' => $position
            ]);
        }

        return view('modules.hr.position-show', compact('position'));
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
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:positions,code',
            'description' => 'nullable|string|max:1000',
            'department_id' => 'nullable|exists:departments,id',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'employment_type' => 'required|string|in:permanent,contract,intern',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
        ]);

        $position = Position::create([
            'title' => $request->title,
            'code' => $request->code,
            'description' => $request->description,
            'department_id' => $request->department_id,
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'employment_type' => $request->employment_type,
            'requirements' => $request->requirements,
            'responsibilities' => $request->responsibilities,
            'is_active' => true,
        ]);

        Log::info('Position created', [
            'position_id' => $position->id,
            'title' => $position->title,
            'created_by' => $user->id
        ]);

        // Log activity
        ActivityLogService::logCreated($position, "Created position: {$position->title}", [
            'title' => $position->title,
            'code' => $position->code,
            'department_id' => $position->department_id,
            'employment_type' => $position->employment_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Position created successfully!',
            'position' => $position->load('department')
        ]);
    }

    public function update(Request $request, Position $position)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:positions,code,' . $position->id,
            'description' => 'nullable|string|max:1000',
            'department_id' => 'nullable|exists:departments,id',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'employment_type' => 'required|string|in:permanent,contract,intern',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $position->update([
            'title' => $request->title,
            'code' => $request->filled('code') ? $request->code : null,
            'description' => $request->filled('description') ? $request->description : null,
            'department_id' => $request->filled('department_id') ? $request->department_id : null,
            'min_salary' => $request->filled('min_salary') ? $request->min_salary : null,
            'max_salary' => $request->filled('max_salary') ? $request->max_salary : null,
            'employment_type' => $request->employment_type,
            'requirements' => $request->filled('requirements') ? $request->requirements : null,
            'responsibilities' => $request->filled('responsibilities') ? $request->responsibilities : null,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : $position->is_active,
        ]);

        Log::info('Position updated', [
            'position_id' => $position->id,
            'title' => $position->title,
            'updated_by' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Position updated successfully!',
            'position' => $position->load('department')
        ]);
    }

    public function destroy(Position $position)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if position has employees (matching by title string)
        $employeeCount = Employee::where('position', $position->title)->count();
        if ($employeeCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete position. There are {$employeeCount} employee(s) with this position."
            ], 422);
        }

        $position->delete();

        Log::info('Position deleted', [
            'position_id' => $position->id,
            'title' => $position->title,
            'deleted_by' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Position deleted successfully!'
        ]);
    }
}
