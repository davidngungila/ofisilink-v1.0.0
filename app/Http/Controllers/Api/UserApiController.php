<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserApiController extends Controller
{
    /**
     * Get list of users with optional filters
     */
    public function index(Request $request)
    {
        $query = User::with(['employee', 'primaryDepartment']);

        // Filter by enrollment status
        if ($request->has('registered')) {
            $query->where('registered_on_device', $request->registered === 'true');
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->whereHas('primaryDepartment', function($q) use ($request) {
                $q->where('id', $request->department_id);
            });
        }

        $users = $query->get();

        return response()->json([
            'success' => true,
            'data' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'employee_id' => $user->employee->employee_id ?? null,
                    'enroll_id' => $user->enroll_id,
                    'registered_on_device' => $user->registered_on_device,
                    'device_registered_at' => $user->device_registered_at?->format('Y-m-d H:i:s'),
                    'department' => $user->primaryDepartment->name ?? null,
                ];
            })
        ]);
    }

    /**
     * Get single user
     */
    public function show($id)
    {
        $user = User::with(['employee', 'primaryDepartment'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee->employee_id ?? null,
                'enroll_id' => $user->enroll_id,
                'registered_on_device' => $user->registered_on_device,
                'device_registered_at' => $user->device_registered_at?->format('Y-m-d H:i:s'),
                'department' => $user->primaryDepartment->name ?? null,
            ]
        ]);
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'enroll_id' => 'required|string|unique:users,enroll_id|regex:/^\d+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt('password123'), // Default password
            'enroll_id' => $request->enroll_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'enroll_id' => $user->enroll_id,
            ]
        ], 201);
    }

    /**
     * Register a User (Simplified)
     * Only `id` and `name` are required. Email and password are auto-generated.
     * POST /api/v1/users/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|regex:/^\d+$/', // Allow existing enroll_id, just validate format
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user with this enroll_id already exists
            $existingUser = User::where('enroll_id', $request->id)->first();
            
            if ($existingUser) {
                // Update name if different
                if ($existingUser->name !== $request->name) {
                    $existingUser->name = $request->name;
                    $existingUser->save();
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'User already exists - updated',
                    'data' => [
                        'id' => $existingUser->id,
                        'name' => $existingUser->name,
                        'enroll_id' => $existingUser->enroll_id,
                        'registered_on_device' => $existingUser->registered_on_device,
                    ]
                ], 200); // Return 200 instead of 201 for existing users
            }

            // Generate email from name and id
            $email = strtolower(str_replace(' ', '.', $request->name)) . '.' . $request->id . '@device.local';
            
            // Ensure email is unique
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = strtolower(str_replace(' ', '.', $request->name)) . '.' . $request->id . '.' . $counter . '@device.local';
                $counter++;
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'password' => bcrypt('password123'), // Default password
                'enroll_id' => $request->id,
                'registered_on_device' => false,
            ]);

            Log::info('User registered via API', [
                'user_id' => $user->id,
                'enroll_id' => $user->enroll_id,
                'name' => $user->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'enroll_id' => $user->enroll_id,
                    'registered_on_device' => false,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('User registration error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Enroll ID for user
     */
    public function generateEnrollId($id)
    {
        try {
            $user = User::with('employee')->findOrFail($id);

            // Check if user already has enroll_id
            if ($user->enroll_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has an Enroll ID: ' . $user->enroll_id
                ], 422);
            }

            // Generate enroll_id from employee_id (extract numeric part)
            // Employee ID format: EMP20251107DU -> Enroll ID: 20251107
            $enrollId = null;
            if ($user->employee && $user->employee->employee_id) {
                // Extract numeric part from employee_id (e.g., "EMP20251107DU" -> "20251107")
                $enrollId = preg_replace('/[^0-9]/', '', $user->employee->employee_id);
                if (empty($enrollId)) {
                    // If no numeric part found, use user id as fallback
                    $enrollId = (string)$user->id;
                }
            } else {
                // If no employee_id, use user id as fallback
                $enrollId = (string)$user->id;
            }

            // Check if enroll_id already exists
            $exists = User::where('enroll_id', $enrollId)->where('id', '!=', $user->id)->exists();
            if ($exists) {
                // Append user id to make it unique
                $enrollId = $enrollId . $user->id;
            }

            $user->enroll_id = $enrollId;
            $user->save();

            Log::info('Enroll ID generated', [
                'user_id' => $user->id,
                'enroll_id' => $enrollId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enroll ID generated successfully',
                'data' => [
                    'id' => $user->id,
                    'enroll_id' => $user->enroll_id,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Generate enroll ID error', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Enroll ID: ' . $e->getMessage()
            ], 500);
        }
    }
}
