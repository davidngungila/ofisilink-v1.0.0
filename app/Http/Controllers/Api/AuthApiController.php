<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Login with email and password
     * 
     * @bodyParam email string required User email
     * @bodyParam password string required User password
     * @bodyParam device_name string optional Device name for token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is inactive. Please contact administrator.'
            ], 403);
        }

        // Generate token
        $deviceName = $request->device_name ?? 'Mobile App';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Login with OTP (request OTP)
     */
    public function loginWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.'
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is inactive. Please contact administrator.'
            ], 403);
        }

        // Check if user has phone number
        $phone = $user->mobile ?? $user->phone;
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number registered for this account.'
            ], 422);
        }

        try {
            // Invalidate previous OTPs
            OtpCode::where('user_id', $user->id)
                ->where('purpose', 'login')
                ->where('used', false)
                ->update(['used' => true, 'used_at' => now()]);

            // Generate 6-digit OTP using secure random_int
            $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Validate OTP code format
            if (strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
                Log::error('OTP generation failed: Invalid OTP code format', [
                    'otp_code' => $otpCode,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating OTP. Please try again.',
                ], 500);
            }

            // Store OTP in database - CRITICAL: Include otp_code field
            $otp = OtpCode::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,  // ← FIXED: Use otp_code not code
                'purpose' => 'login',
                'expires_at' => now()->addMinutes(10),
                'ip_address' => $request->ip(),
                'used' => false,
            ]);

            // Verify OTP was created
            if (!$otp || !$otp->id) {
                throw new \Exception('OTP record was not created successfully');
            }

            // Send OTP via SMS (always for mobile API)
            $sendSms = $request->input('send_sms', true) || $request->input('platform') === 'mobile';
            
            if ($sendSms && $phone) {
                $message = "Your OfisiLink login OTP is: {$otpCode}. Valid for 10 minutes.";
                $smsSent = $this->notificationService->sendSMS($phone, $message);
                
                if (!$smsSent) {
                    Log::warning('OTP created but SMS sending failed', [
                        'user_id' => $user->id,
                        'phone' => $phone,
                        'otp_code' => $otpCode
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP has been sent to your registered phone number.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error creating OTP', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $user->id ?? null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Database error. Please contact administrator.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error creating OTP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating OTP. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify OTP and login
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Verify OTP - USE 'otp_code' NOT 'code'
        $otp = OtpCode::where('user_id', $user->id)
            ->where('otp_code', $request->otp)  // ← FIXED: Use otp_code not code
            ->where('purpose', 'login')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code.'
            ], 422);
        }

        // Mark OTP as used
        $otp->update(['used' => true, 'used_at' => now()]);

        // Generate token
        $deviceName = $request->device_name ?? 'Mobile App';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.'
            ], 404);
        }

        // Check if user has phone number
        $phone = $user->mobile ?? $user->phone;
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number registered for this account.'
            ], 422);
        }

        try {
            // Invalidate previous OTPs
            OtpCode::where('user_id', $user->id)
                ->where('purpose', 'login')
                ->where('used', false)
                ->update(['used' => true, 'used_at' => now()]);

            // Generate 6-digit OTP using secure random_int
            $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Validate OTP code format
            if (strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
                Log::error('OTP generation failed: Invalid OTP code format', [
                    'otp_code' => $otpCode,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating OTP. Please try again.',
                ], 500);
            }

            // Create new OTP - CRITICAL: Include otp_code field
            $otp = OtpCode::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,  // ← FIXED: Use otp_code not code
                'purpose' => 'login',
                'expires_at' => now()->addMinutes(10),
                'ip_address' => $request->ip(),
                'used' => false,
            ]);

            // Verify OTP was created
            if (!$otp || !$otp->id) {
                throw new \Exception('OTP record was not created successfully');
            }

            // Send OTP via SMS (always for mobile API)
            $sendSms = $request->input('send_sms', true) || $request->input('platform') === 'mobile';
            
            if ($sendSms && $phone) {
                $message = "Your OfisiLink login OTP is: {$otpCode}. Valid for 10 minutes.";
                $smsSent = $this->notificationService->sendSMS($phone, $message);
                
                if (!$smsSent) {
                    Log::warning('OTP created but SMS sending failed', [
                        'user_id' => $user->id,
                        'phone' => $phone,
                        'otp_code' => $otpCode
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP has been resent to your registered phone number.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error creating OTP', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $user->id ?? null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Database error. Please contact administrator.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error creating OTP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating OTP. Please try again.',
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $this->formatUser($user)
        ]);
    }

    /**
     * Logout (revoke current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Refresh token (create new token and revoke old)
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $deviceName = $request->device_name ?? 'Mobile App';

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (!$user) {
            // Don't reveal if user exists
            return response()->json([
                'success' => true,
                'message' => 'If the email exists, a password reset link has been sent.'
            ]);
        }

        // Check if user has phone number
        $phone = $user->mobile ?? $user->phone;
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number registered for this account.'
            ], 422);
        }

        try {
            // Invalidate previous password reset OTPs
            OtpCode::where('user_id', $user->id)
                ->where('purpose', 'password_reset')
                ->where('used', false)
                ->update(['used' => true, 'used_at' => now()]);

            // Generate 6-digit OTP using secure random_int
            $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Validate OTP code format
            if (strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
                Log::error('OTP generation failed: Invalid OTP code format', [
                    'otp_code' => $otpCode,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating OTP. Please try again.',
                ], 500);
            }

            // Create OTP for password reset - CRITICAL: Include otp_code field
            $otp = OtpCode::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,  // ← FIXED: Use otp_code not code
                'purpose' => 'password_reset',
                'expires_at' => now()->addMinutes(30),
                'ip_address' => $request->ip(),
                'used' => false,
            ]);

            // Verify OTP was created
            if (!$otp || !$otp->id) {
                throw new \Exception('OTP record was not created successfully');
            }

            // Send OTP via SMS
            if ($phone) {
                $message = "Your OfisiLink password reset OTP is: {$otpCode}. Valid for 30 minutes.";
                $smsSent = $this->notificationService->sendSMS($phone, $message);
                
                if (!$smsSent) {
                    Log::warning('OTP created but SMS sending failed', [
                        'user_id' => $user->id,
                        'phone' => $phone,
                        'otp_code' => $otpCode
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Password reset OTP has been sent to your registered phone number.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error creating OTP', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $user->id ?? null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Database error. Please contact administrator.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error creating OTP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating OTP. Please try again.',
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Verify OTP - USE 'otp_code' NOT 'code'
        $otp = OtpCode::where('user_id', $user->id)
            ->where('otp_code', $request->otp)  // ← FIXED: Use otp_code not code
            ->where('purpose', 'password_reset')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code.'
            ], 422);
        }

        // Mark OTP as used
        $otp->update(['used' => true, 'used_at' => now()]);

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Format user data for API response
     */
    private function formatUser($user)
    {
        // Load relationships to avoid N+1 queries
        if (!$user->relationLoaded('primaryDepartment')) {
            $user->load('primaryDepartment');
        }
        if (!$user->relationLoaded('roles')) {
            $user->load('roles.permissions');
        }
        
        // Get permissions through roles (avoiding Spatie's getAllPermissions which has issues)
        $permissions = collect();
        if ($user->relationLoaded('roles') && $user->roles) {
            foreach ($user->roles as $role) {
                if ($role->relationLoaded('permissions') && $role->permissions) {
                    $permissions = $permissions->merge($role->permissions);
                }
            }
        }
        $permissions = $permissions->unique('id');
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'employee_id' => $user->employee_id,
            'photo' => $user->photo ? url('/storage/photos/' . $user->photo) : null,
            'primary_department' => $user->primaryDepartment ? [
                'id' => $user->primaryDepartment->id,
                'name' => $user->primaryDepartment->name,
            ] : null,
            'roles' => $user->roles ? $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? $role->name,
                ];
            }) : [],
            'permissions' => $permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name ?? $permission->name,
                ];
            })->values(),
        ];
    }
}

