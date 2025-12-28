<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DeviceTokenApiController extends Controller
{
    /**
     * Register or update device token
     * 
     * POST /api/mobile/v1/device/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:500',
            'device_type' => 'required|in:ios,android,web',
            'device_id' => 'nullable|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
            'os_version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        try {
            // Check if token already exists for this user
            $deviceToken = DeviceToken::where('token', $request->token)
                ->where('user_id', $user->id)
                ->first();

            if ($deviceToken) {
                // Update existing token
                $deviceToken->update([
                    'device_type' => $request->device_type,
                    'device_id' => $request->device_id,
                    'device_name' => $request->device_name,
                    'app_version' => $request->app_version,
                    'os_version' => $request->os_version,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Device token updated successfully',
                    'data' => [
                        'id' => $deviceToken->id,
                        'device_type' => $deviceToken->device_type,
                        'device_name' => $deviceToken->device_name,
                    ]
                ]);
            } else {
                // Check if user has too many tokens (limit to 5 per user)
                $tokenCount = DeviceToken::where('user_id', $user->id)->count();
                if ($tokenCount >= 5) {
                    // Deactivate oldest inactive token
                    $oldestToken = DeviceToken::where('user_id', $user->id)
                        ->orderBy('last_used_at', 'asc')
                        ->first();
                    if ($oldestToken) {
                        $oldestToken->deactivate();
                    }
                }

                // Create new token
                $deviceToken = DeviceToken::create([
                    'user_id' => $user->id,
                    'token' => $request->token,
                    'device_type' => $request->device_type,
                    'device_id' => $request->device_id,
                    'device_name' => $request->device_name,
                    'app_version' => $request->app_version,
                    'os_version' => $request->os_version,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Device token registered successfully',
                    'data' => [
                        'id' => $deviceToken->id,
                        'device_type' => $deviceToken->device_type,
                        'device_name' => $deviceToken->device_name,
                    ]
                ], 201);
            }
        } catch (\Exception $e) {
            Log::error('Error registering device token', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register device token'
            ], 500);
        }
    }

    /**
     * Unregister device token
     * 
     * DELETE /api/mobile/v1/device/unregister
     */
    public function unregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $deviceToken = DeviceToken::where('token', $request->token)
            ->where('user_id', $user->id)
            ->first();

        if ($deviceToken) {
            $deviceToken->deactivate();

            return response()->json([
                'success' => true,
                'message' => 'Device token unregistered successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Device token not found'
        ], 404);
    }

    /**
     * Get user's device tokens
     * 
     * GET /api/mobile/v1/device/tokens
     */
    public function tokens()
    {
        $user = Auth::user();

        $tokens = DeviceToken::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'device_type' => $token->device_type,
                    'device_name' => $token->device_name,
                    'app_version' => $token->app_version,
                    'os_version' => $token->os_version,
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'created_at' => $token->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tokens
        ]);
    }

    /**
     * Update device token info
     * 
     * PUT /api/mobile/v1/device/tokens/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
            'os_version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $deviceToken = DeviceToken::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $deviceToken->update($request->only(['device_name', 'app_version', 'os_version']));
        $deviceToken->markAsUsed();

        return response()->json([
            'success' => true,
            'message' => 'Device token updated successfully',
            'data' => [
                'id' => $deviceToken->id,
                'device_name' => $deviceToken->device_name,
                'app_version' => $deviceToken->app_version,
                'os_version' => $deviceToken->os_version,
            ]
        ]);
    }
}




