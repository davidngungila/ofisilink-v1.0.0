<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileApiController extends Controller
{
    /**
     * Get user profile
     */
    public function show(Request $request)
    {
        $user = Auth::user()->load(['primaryDepartment', 'roles', 'employee']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'mobile' => $user->mobile,
                'employee_id' => $user->employee_id,
                'photo' => $user->photo ? url('/storage/photos/' . $user->photo) : null,
                'date_of_birth' => $user->date_of_birth,
                'gender' => $user->gender,
                'marital_status' => $user->marital_status,
                'nationality' => $user->nationality,
                'address' => $user->address,
                'hire_date' => $user->hire_date,
                'primary_department' => $user->primaryDepartment ? [
                    'id' => $user->primaryDepartment->id,
                    'name' => $user->primaryDepartment->name,
                ] : null,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'mobile' => 'sometimes|nullable|string|max:20',
            'date_of_birth' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'marital_status' => 'sometimes|nullable|string',
            'nationality' => 'sometimes|nullable|string|max:100',
            'address' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'name', 'phone', 'mobile', 'date_of_birth', 
            'gender', 'marital_status', 'nationality', 'address'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $this->formatUser($user)
        ]);
    }

    /**
     * Update profile photo
     */
    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Delete old photo if exists
        if ($user->photo && Storage::disk('public')->exists('photos/' . $user->photo)) {
            Storage::disk('public')->delete('photos/' . $user->photo);
        }

        // Store new photo
        $photo = $request->file('photo');
        $filename = time() . '_' . $user->id . '.' . $photo->getClientOriginalExtension();
        $photo->storeAs('photos', $filename, 'public');

        $user->update(['photo' => $filename]);

        return response()->json([
            'success' => true,
            'message' => 'Photo updated successfully',
            'data' => [
                'photo' => url('/storage/photos/' . $filename)
            ]
        ]);
    }

    private function formatUser($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'photo' => $user->photo ? url('/storage/photos/' . $user->photo) : null,
        ];
    }
}







