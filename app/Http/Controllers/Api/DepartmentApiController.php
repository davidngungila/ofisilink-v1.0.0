<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentApiController extends Controller
{
    /**
     * Get all departments
     */
    public function index(Request $request)
    {
        $departments = Department::where('is_active', true)
            ->with(['head:id,name,email'])
            ->orderBy('name')
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'code' => $department->code,
                    'description' => $department->description,
                    'head' => $department->head ? [
                        'id' => $department->head->id,
                        'name' => $department->head->name,
                        'email' => $department->head->email,
                    ] : null,
                    'member_count' => $department->primaryUsers()->where('is_active', true)->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    /**
     * Get single department
     */
    public function show($id)
    {
        $department = Department::with(['head:id,name,email'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'head' => $department->head ? [
                    'id' => $department->head->id,
                    'name' => $department->head->name,
                    'email' => $department->head->email,
                ] : null,
                'member_count' => $department->primaryUsers()->where('is_active', true)->count(),
                'is_active' => $department->is_active,
            ]
        ]);
    }

    /**
     * Get department members
     */
    public function members($id)
    {
        $department = Department::findOrFail($id);
        
        $members = $department->primaryUsers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'employee_id' => $user->employee_id,
                    'photo' => $user->photo ? url('/storage/photos/' . $user->photo) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }
}







