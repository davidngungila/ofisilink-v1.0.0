<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OtpCode;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class AuthController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get OTP timeout in minutes from system settings
     * Defaults to 10 minutes if not configured
     */
    protected function getOtpTimeout()
    {
        return SystemSetting::getValue('otp_timeout_minutes', 10);
    }

    /**
     * Get max login attempts from system settings
     * Defaults to 5 attempts if not configured
     */
    protected function getMaxLoginAttempts()
    {
        return (int) SystemSetting::getValue('max_login_attempts', 5);
    }

    /**
     * Check if user account is locked due to failed login attempts
     */
    protected function isAccountLocked($user)
    {
        if (!$user->locked_until) {
            return false;
        }
        
        // Check if lock period has expired
        if (now()->isAfter($user->locked_until)) {
            // Lock expired, reset attempts
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_failed_login_at' => null,
            ]);
            return false;
        }
        
        return true;
    }

    /**
     * Increment failed login attempts and lock account if threshold reached
     */
    protected function handleFailedLogin($user)
    {
        $maxAttempts = $this->getMaxLoginAttempts();
        $failedAttempts = ($user->failed_login_attempts ?? 0) + 1;
        
        $updateData = [
            'failed_login_attempts' => $failedAttempts,
            'last_failed_login_at' => now(),
        ];
        
        // Lock account if max attempts reached
        if ($failedAttempts >= $maxAttempts) {
            // Lock for 30 minutes
            $updateData['locked_until'] = now()->addMinutes(30);
            
            Log::warning('User account locked due to max login attempts', [
                'user_id' => $user->id,
                'email' => $user->email,
                'failed_attempts' => $failedAttempts,
                'max_attempts' => $maxAttempts,
                'locked_until' => $updateData['locked_until'],
                'ip' => request()->ip()
            ]);
        }
        
        $user->update($updateData);
    }

    /**
     * Reset failed login attempts on successful login
     */
    protected function resetLoginAttempts($user)
    {
        if ($user->failed_login_attempts > 0 || $user->locked_until) {
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_failed_login_at' => null,
            ]);
        }
    }

    public function showLoginForm()
    {
        // If already authenticated as System Admin, allow through
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
                // System Admin can proceed - middleware will handle it
                return redirect()->route('dashboard');
            }
            // Other authenticated users during maintenance should see login too
            // (they can try to login as admin if they have admin account)
        }
        
        // Show login form - accessible during maintenance for System Admins
        return view('auth.login');
    }

    /**
     * Handle OTP-only login (without password)
     */
    public function loginWithOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find user by email (case-insensitive)
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        // Check if user exists
        if (!$user) {
            Log::warning('OTP-only login attempt with non-existent email', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->onlyInput('email');
        }

        // Check if account is locked due to failed login attempts
        if ($this->isAccountLocked($user)) {
            $remainingMinutes = now()->diffInMinutes($user->locked_until, false);
            $lockMessage = 'Your account has been temporarily locked due to too many failed login attempts. Please try again in ' . $remainingMinutes . ' minute(s).';
            
            Log::warning('OTP-only login attempt for locked account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'locked_until' => $user->locked_until,
                'failed_attempts' => $user->failed_login_attempts,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'email' => $lockMessage,
            ])->onlyInput('email');
        }

        // Check if user is active
        if (!$user->is_active) {
            Log::warning('OTP-only login attempt for inactive user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact administrator.',
            ])->onlyInput('email');
        }

        // Check if user is blocked
        if ($user->blocked_at) {
            $isBlocked = false;
            if (!$user->blocked_until) {
                $isBlocked = true;
                $blockMessage = 'Your account has been permanently blocked.';
            } else {
                $isBlocked = now()->isBefore($user->blocked_until);
                if ($isBlocked) {
                    $blockMessage = 'Your account has been blocked until ' . $user->blocked_until->format('Y-m-d H:i:s') . '.';
                    if ($user->block_reason) {
                        $blockMessage .= ' Reason: ' . $user->block_reason;
                    }
                }
            }
            
            if ($isBlocked) {
                Log::warning('OTP-only login attempt for blocked user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);
                return back()->withErrors([
                    'email' => $blockMessage,
                ])->onlyInput('email');
            } else {
                // Block period expired, unblock user
                $user->update([
                    'blocked_at' => null,
                    'blocked_until' => null,
                    'block_reason' => null,
                    'blocked_by' => null,
                ]);
            }
        }

        // Check if user has a phone number
        $phone = $user->mobile ?? $user->phone;
        if (!$phone) {
            return back()->withErrors([
                'email' => 'No phone number registered. Please contact administrator.',
            ])->onlyInput('email');
        }

        // CRITICAL: Check if user has employee record - no login without employee details
        if (!$user->employee) {
            Log::warning('OTP-only login attempt for user without employee record', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'email' => 'Your account is not properly configured. Employee details are required before login. Please contact HR or System Administrator to complete your employee registration.',
            ])->onlyInput('email');
        }

        try {
            // Get OTP timeout from settings
            $otpTimeout = $this->getOtpTimeout();

            // Invalidate previous OTPs for login
            OtpCode::where('user_id', $user->id)
                ->where('purpose', 'login')
                ->where('used', false)
                ->update(['used' => true, 'used_at' => now()]);

            // Generate 6-digit OTP
            $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Validate OTP code format
            if (strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
                Log::error('OTP generation failed: Invalid OTP code format', [
                    'otp_code' => $otpCode,
                    'user_id' => $user->id
                ]);
                return back()->withErrors([
                    'email' => 'Error generating OTP. Please try again.',
                ])->onlyInput('email');
            }

            // Store OTP in database with error handling
            try {
                $otp = OtpCode::create([
                    'user_id' => $user->id,
                    'otp_code' => $otpCode,
                    'expires_at' => now()->addMinutes($otpTimeout),
                    'purpose' => 'login',
                    'ip_address' => $request->ip(),
                    'used' => false,
                ]);

                // Verify OTP was created
                if (!$otp || !$otp->id) {
                    throw new \Exception('OTP record was not created successfully');
                }

                Log::info('OTP created successfully (OTP-only login)', [
                    'otp_id' => $otp->id,
                    'user_id' => $user->id,
                    'purpose' => 'login',
                    'expires_at' => $otp->expires_at
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error creating OTP', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'user_id' => $user->id,
                    'sql_state' => $e->errorInfo[0] ?? null,
                    'driver_code' => $e->errorInfo[1] ?? null,
                ]);
                
                return back()->withErrors([
                    'email' => 'Database error. Please contact administrator.',
                ])->onlyInput('email');
            } catch (\Exception $e) {
                Log::error('Error creating OTP', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id
                ]);
                
                return back()->withErrors([
                    'email' => 'Error generating OTP. Please try again.',
                ])->onlyInput('email');
            }

            // Send OTP via SMS
            $message = "Your OfisiLink login OTP is: {$otpCode}. Valid for {$otpTimeout} minutes.";
            $smsSent = $this->notificationService->sendSMS($phone, $message);
            
            if (!$smsSent) {
                Log::warning('OTP created but SMS sending failed', [
                    'user_id' => $user->id,
                    'phone' => $phone,
                    'otp_code' => $otpCode
                ]);
                // Continue anyway - user can request resend
            }

            // Send OTP via Email with professional template
            if ($user->email) {
                try {
                    $emailSubject = 'Login OTP Verification - OfisiLink';
                    $emailBody = View::make('emails.otp', [
                        'otpCode' => $otpCode,
                        'userName' => $user->name,
                        'purpose' => 'login',
                        'validityMinutes' => $otpTimeout,
                        'loginUrl' => route('login.otp'),
                        'subject' => $emailSubject
                    ])->render();
                    
                    $emailSent = $this->notificationService->sendEmail(
                        $user->email,
                        $emailSubject,
                        $emailBody,
                        [
                            'type' => 'otp',
                            'purpose' => 'login',
                            'otp_code' => $otpCode,
                            'is_html' => true
                        ]
                    );
                    
                    if ($emailSent) {
                        Log::info('OTP email sent successfully (OTP-only login)', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    } else {
                        Log::warning('OTP created but email sending failed', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error sending OTP email', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                    // Continue anyway - SMS was sent
                }
            }

        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Base table or view not found')) {
                Log::error('Database table missing for OTP codes', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id ?? null
                ]);
                return back()->withErrors([
                    'email' => 'System configuration error: OTP table not found. Please contact administrator.',
                ])->onlyInput('email');
            }
            
            Log::error('Database error in OTP generation process', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $user->id ?? null,
            ]);
            
            return back()->withErrors([
                'email' => 'Database error occurred. Please contact administrator.',
            ])->onlyInput('email');
        } catch (\Exception $e) {
            Log::error('Fatal error in OTP generation process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);
            
            return back()->withErrors([
                'email' => 'An unexpected error occurred: ' . $e->getMessage() . '. Please try again or contact administrator.',
            ])->onlyInput('email');
        }

        // Store user ID in session for OTP verification
        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_remember', $request->boolean('remember'));

        // Redirect to OTP verification page
        return redirect()->route('login.otp')->with('success', 'OTP has been sent to your registered phone number.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find user by email (case-insensitive)
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($credentials['email'])])->first();

        // Check if user exists
        if (!$user) {
            Log::warning('Login attempt with non-existent email', [
                'email' => $credentials['email'],
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'email' => 'Invalid credentials. Please check your email and password.',
            ])->onlyInput('email');
        }

        // Check if user is active
        if (!$user->is_active) {
            Log::warning('Login attempt for inactive user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact administrator.',
            ])->onlyInput('email');
        }

        // Check if account is locked due to failed login attempts
        if ($this->isAccountLocked($user)) {
            $remainingMinutes = now()->diffInMinutes($user->locked_until, false);
            $lockMessage = 'Your account has been temporarily locked due to too many failed login attempts. Please try again in ' . $remainingMinutes . ' minute(s).';
            
            Log::warning('Login attempt for locked account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'locked_until' => $user->locked_until,
                'failed_attempts' => $user->failed_login_attempts,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'email' => $lockMessage,
            ])->onlyInput('email');
        }

        // Check if user is blocked
        if ($user->blocked_at) {
            $isBlocked = false;
            if (!$user->blocked_until) {
                // Forever blocked
                $isBlocked = true;
                $blockMessage = 'Your account has been permanently blocked.';
            } else {
                // Check if block period has expired
                $isBlocked = now()->isBefore($user->blocked_until);
                if ($isBlocked) {
                    $blockMessage = 'Your account has been blocked until ' . $user->blocked_until->format('Y-m-d H:i:s') . '.';
                    if ($user->block_reason) {
                        $blockMessage .= ' Reason: ' . $user->block_reason;
                    }
                }
            }
            
            if ($isBlocked) {
                Log::warning('Login attempt for blocked user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'blocked_at' => $user->blocked_at,
                    'blocked_until' => $user->blocked_until,
                    'ip' => $request->ip()
                ]);
                return back()->withErrors([
                    'email' => $blockMessage,
                ])->onlyInput('email');
            } else {
                // Block period expired, unblock user
                $user->update([
                    'blocked_at' => null,
                    'blocked_until' => null,
                    'block_reason' => null,
                    'blocked_by' => null,
                ]);
            }
        }

        // Check if user has a password set
        if (empty($user->password)) {
            Log::warning('Login attempt for user without password', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'email' => 'Your account is not properly configured. Please contact administrator to set up your password.',
            ])->onlyInput('email');
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            // Increment failed login attempts
            $this->handleFailedLogin($user);
            
            $maxAttempts = $this->getMaxLoginAttempts();
            $remainingAttempts = $maxAttempts - ($user->fresh()->failed_login_attempts ?? 0);
            
            $errorMessage = 'Invalid credentials. Please check your email and password.';
            if ($remainingAttempts > 0 && $remainingAttempts < $maxAttempts) {
                $errorMessage .= ' ' . $remainingAttempts . ' attempt(s) remaining before account lockout.';
            }
            
            Log::warning('Login attempt with incorrect password', [
                'user_id' => $user->id,
                'email' => $user->email,
                'failed_attempts' => $user->fresh()->failed_login_attempts,
                'max_attempts' => $maxAttempts,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'email' => $errorMessage,
            ])->onlyInput('email');
        }

        // Password is correct - reset failed login attempts
        $this->resetLoginAttempts($user);

        // Check if user has a phone number
        $phone = $user->mobile ?? $user->phone;
        if (!$phone) {
            return back()->withErrors([
                'email' => 'No phone number registered. Please contact administrator.',
            ])->onlyInput('email');
        }

        // CRITICAL: Check if user has employee record - no login without employee details
        if (!$user->employee) {
            Log::warning('Login attempt for user without employee record', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'email' => 'Your account is not properly configured. Employee details are required before login. Please contact HR or System Administrator to complete your employee registration.',
            ])->onlyInput('email');
        }

        try {
            // Get OTP timeout from settings
            $otpTimeout = $this->getOtpTimeout();

            // Invalidate previous OTPs for login
            OtpCode::where('user_id', $user->id)
                ->where('purpose', 'login')
                ->where('used', false)
                ->update(['used' => true, 'used_at' => now()]);

            // Generate 6-digit OTP
            $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Validate OTP code format
            if (strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
                Log::error('OTP generation failed: Invalid OTP code format', [
                    'otp_code' => $otpCode,
                    'user_id' => $user->id
                ]);
                return back()->withErrors([
                    'email' => 'Error generating OTP. Please try again.',
                ])->onlyInput('email');
            }

            // Store OTP in database with error handling
            try {
                $otp = OtpCode::create([
                    'user_id' => $user->id,
                    'otp_code' => $otpCode,
                    'expires_at' => now()->addMinutes($otpTimeout),
                    'purpose' => 'login',
                    'ip_address' => $request->ip(),
                    'used' => false, // Explicitly set to ensure default
                ]);

                // Verify OTP was created
                if (!$otp || !$otp->id) {
                    throw new \Exception('OTP record was not created successfully');
                }

                Log::info('OTP created successfully', [
                    'otp_id' => $otp->id,
                    'user_id' => $user->id,
                    'purpose' => 'login',
                    'expires_at' => $otp->expires_at
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error creating OTP', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'user_id' => $user->id,
                    'sql_state' => $e->errorInfo[0] ?? null,
                    'driver_code' => $e->errorInfo[1] ?? null,
                ]);
                
                return back()->withErrors([
                    'email' => 'Database error. Please contact administrator.',
                ])->onlyInput('email');
            } catch (\Exception $e) {
                Log::error('Error creating OTP', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id
                ]);
                
                return back()->withErrors([
                    'email' => 'Error generating OTP. Please try again.',
                ])->onlyInput('email');
            }

            // Send OTP via SMS
            $message = "Your OfisiLink login OTP is: {$otpCode}. Valid for {$otpTimeout} minutes.";
            $smsSent = $this->notificationService->sendSMS($phone, $message);
            
            if (!$smsSent) {
                Log::warning('OTP created but SMS sending failed', [
                    'user_id' => $user->id,
                    'phone' => $phone,
                    'otp_code' => $otpCode
                ]);
                // Continue anyway - user can request resend
            }

            // Send OTP via Email with professional template
            if ($user->email) {
                try {
                    $emailSubject = 'Login OTP Verification - OfisiLink';
                    $emailBody = View::make('emails.otp', [
                        'otpCode' => $otpCode,
                        'userName' => $user->name,
                        'purpose' => 'login',
                        'validityMinutes' => $otpTimeout,
                        'loginUrl' => route('login.otp'),
                        'subject' => $emailSubject
                    ])->render();
                    
                    $emailSent = $this->notificationService->sendEmail(
                        $user->email,
                        $emailSubject,
                        $emailBody,
                        [
                            'type' => 'otp',
                            'purpose' => 'login',
                            'otp_code' => $otpCode,
                            'is_html' => true  // Indicate this is already HTML
                        ]
                    );
                    
                    if ($emailSent) {
                        Log::info('OTP email sent successfully', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    } else {
                        Log::warning('OTP created but email sending failed', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error sending OTP email', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                    // Continue anyway - SMS was sent
                }
            }

        } catch (\Illuminate\Database\QueryException $e) {
            // Check if it's a missing table error
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Base table or view not found')) {
                Log::error('Database table missing for OTP codes', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id ?? null
                ]);
                return back()->withErrors([
                    'email' => 'System configuration error: OTP table not found. Please contact administrator.',
                ])->onlyInput('email');
            }
            
            Log::error('Database error in OTP generation process', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $user->id ?? null,
                'sql_state' => $e->errorInfo[0] ?? null,
                'driver_code' => $e->errorInfo[1] ?? null,
            ]);
            
            return back()->withErrors([
                'email' => 'Database error occurred. Please contact administrator.',
            ])->onlyInput('email');
        } catch (\Exception $e) {
            Log::error('Fatal error in OTP generation process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);
            
            return back()->withErrors([
                'email' => 'An unexpected error occurred: ' . $e->getMessage() . '. Please try again or contact administrator.',
            ])->onlyInput('email');
        }

        // Store user ID in session for OTP verification
        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_remember', $request->boolean('remember'));

        // Redirect to OTP verification page
        return redirect()->route('login.otp')->with('success', 'OTP has been sent to your registered phone number.');





        // Redirect to OTP verification page
        //return redirect()->route('login.otp')->with('success', 'OTP has been sent to your registered phone number.');
    }

    public function showOtpForm()
    {
        if (!session('otp_user_id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Please login first.',
            ]);
        }
        return view('auth.login');
    }

    /**
     * Handle GET requests to OTP verify route (when session expires)
     * Redirects to login page
     */
    public function redirectOtpVerify()
    {
        // Clear any stale OTP session data
        session()->forget(['otp_user_id', 'otp_remember']);
        
        return redirect()->route('login')->withErrors([
            'email' => 'Session expired. Please login again.',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('otp_user_id');
        
        if (!$userId) {
            return back()->withErrors([
                'otp_code' => 'Session expired. Please login again.',
            ]);
        }

        // Get user first to check lock status
        $user = User::findOrFail($userId);

        // Verify OTP
        $otp = OtpCode::where('user_id', $userId)
            ->where('purpose', 'login')
            ->where('otp_code', $request->otp_code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            // Increment failed login attempts for invalid OTP
            $this->handleFailedLogin($user);
            
            // Refresh user to get updated lock status
            $user->refresh();
            
            // Check if account is now locked
            if ($this->isAccountLocked($user)) {
                $remainingMinutes = now()->diffInMinutes($user->locked_until, false);
                return back()->withErrors([
                    'otp_code' => 'Invalid or expired OTP code. Your account has been temporarily locked due to too many failed attempts. Please try again in ' . $remainingMinutes . ' minute(s).',
                ]);
            }
            
            $maxAttempts = $this->getMaxLoginAttempts();
            $remainingAttempts = $maxAttempts - ($user->failed_login_attempts ?? 0);
            $errorMessage = 'Invalid or expired OTP code.';
            if ($remainingAttempts > 0 && $remainingAttempts < $maxAttempts) {
                $errorMessage .= ' ' . $remainingAttempts . ' attempt(s) remaining before account lockout.';
            }
            
            return back()->withErrors([
                'otp_code' => $errorMessage,
            ]);
        }

        // CRITICAL: Final check before login - ensure employee record exists
        if (!$user->employee) {
            Log::warning('OTP verification attempt for user without employee record', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'otp_code' => 'Your account is not properly configured. Employee details are required before login. Please contact HR or System Administrator to complete your employee registration.',
            ]);
        }

        // Mark OTP as used
        $otp->update(['used' => true, 'used_at' => now()]);

        // Reset failed login attempts on successful OTP verification
        $this->resetLoginAttempts($user);

        // Ensure roles are loaded before login
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }
        
        // Login the user
        Auth::login($user, session('otp_remember', false));
        
        // Log successful login
        ActivityLogService::logLogin($user, [
            'login_method' => 'otp',
            'remember_me' => session('otp_remember', false),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        $request->session()->regenerate();
        $request->session()->forget(['otp_user_id', 'otp_remember']);
        
        // Store user roles in session for easy access
        $userRoles = $user->roles->pluck('name')->toArray();
        session(['user_roles' => $userRoles]);
        session(['user_id' => $user->id]);
        
        // Redirect based on user's highest role
        return $this->redirectBasedOnRole($user);
    }

    public function resendOtp(Request $request)
    {
        $userId = session('otp_user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.',
            ], 422);
        }

        $user = User::findOrFail($userId);
        $phone = $user->mobile ?? $user->phone;
        
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number registered.',
            ], 422);
        }

        try {
            // Get OTP timeout from settings
            $otpTimeout = $this->getOtpTimeout();

            // Invalidate previous OTPs
            OtpCode::where('user_id', $user->id)
                ->where('purpose', 'login')
                ->where('used', false)
                ->update(['used' => true, 'used_at' => now()]);

            // Generate new OTP
            $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Validate OTP code format
            if (strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
                Log::error('OTP generation failed in resend: Invalid OTP code format', [
                    'otp_code' => $otpCode,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating OTP. Please try again.',
                ], 422);
            }

            // Store OTP in database
            try {
                $otp = OtpCode::create([
                    'user_id' => $user->id,
                    'otp_code' => $otpCode,
                    'expires_at' => now()->addMinutes($otpTimeout),
                    'purpose' => 'login',
                    'ip_address' => $request->ip(),
                    'used' => false,
                ]);

                if (!$otp || !$otp->id) {
                    throw new \Exception('OTP record was not created successfully');
                }

                Log::info('OTP resent successfully', [
                    'otp_id' => $otp->id,
                    'user_id' => $user->id
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error creating OTP in resend', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'user_id' => $user->id,
                    'sql_state' => $e->errorInfo[0] ?? null,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Database error. Please contact administrator.',
                ], 422);
            } catch (\Exception $e) {
                Log::error('Error creating OTP in resend', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating OTP. Please try again.',
                ], 422);
            }

            // Send OTP via SMS
            $message = "Your OfisiLink login OTP is: {$otpCode}. Valid for 10 minutes.";
            $smsSent = $this->notificationService->sendSMS($phone, $message);
            
            if (!$smsSent) {
                Log::warning('OTP created but SMS sending failed in resend', [
                    'user_id' => $user->id,
                    'phone' => $phone
                ]);
            }

            // Send OTP via Email with professional template
            if ($user->email) {
                try {
                    $emailSubject = 'Login OTP Verification - OfisiLink (Resent)';
                    $emailBody = View::make('emails.otp', [
                        'otpCode' => $otpCode,
                        'userName' => $user->name,
                        'purpose' => 'login',
                        'validityMinutes' => $otpTimeout,
                        'loginUrl' => route('login.otp'),
                        'subject' => $emailSubject
                    ])->render();
                    
                    $emailSent = $this->notificationService->sendEmail(
                        $user->email,
                        $emailSubject,
                        $emailBody,
                        [
                            'type' => 'otp',
                            'purpose' => 'login_resend',
                            'otp_code' => $otpCode,
                            'is_html' => true  // Indicate this is already HTML
                        ]
                    );
                    
                    if ($emailSent) {
                        Log::info('OTP email resent successfully', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error resending OTP email', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Fatal error in OTP resend process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP has been resent to your registered phone number.',
        ]);
    }

    private function redirectBasedOnRole($user)
    {
        $roles = $user->roles()->pluck('name')->toArray();
        
        if (in_array('System Admin', $roles)) {
            return redirect()->route('admin.dashboard');
        } elseif (in_array('CEO', $roles) || in_array('Director', $roles)) {
            return redirect()->route('ceo.dashboard');
        } elseif (in_array('HOD', $roles)) {
            return redirect()->route('hod.dashboard');
        } elseif (in_array('Accountant', $roles)) {
            return redirect()->route('accountant.dashboard');
        } elseif (in_array('HR Officer', $roles)) {
            return redirect()->route('hr.dashboard');
        } else {
            return redirect()->route('staff.dashboard');
        }
    }

    public function logout(Request $request)
    {
        // Check if user is authenticated before attempting logout
        if (Auth::check()) {
            $user = Auth::user();
            Auth::logout();
            
            // Log logout
            if ($user) {
                ActivityLogService::logLogout($user, [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        return redirect()->route('login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.login');
    }

    /**
     * Request password reset - Step 1: Verify username and return masked phone
     */
    public function requestPasswordReset(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
        ]);

        // Find user by email (case-insensitive)
        // Note: Users can enter their email as "username"
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->username)])->first();

        if (!$user) {
            return back()->withErrors([
                'password_reset_username' => 'No account found with this username.',
            ])->onlyInput('username');
        }

        // Check if user has a phone number
        $phone = $user->mobile ?? $user->phone;
        if (!$phone) {
            return back()->withErrors([
                'password_reset_username' => 'No phone number registered. Please contact administrator.',
            ])->onlyInput('username');
        }

        // Mask phone number (show only last 3 digits)
        $maskedPhone = $this->maskPhoneNumber($phone);

        // Store user ID and phone number in session for next step
        session([
            'password_reset_user_id' => $user->id,
            'password_reset_phone' => $phone,
            'password_reset_masked_phone' => $maskedPhone
        ]);

        // Redirect to phone confirmation page
        return redirect()->route('login')->with([
            'password_reset_phone_confirm' => true,
            'password_reset_masked_phone' => $maskedPhone
        ]);
    }

    /**
     * Confirm phone number and send OTP - Step 2
     */
    public function confirmPhoneAndSendOtp(Request $request)
    {
        $userId = session('password_reset_user_id');
        $phone = session('password_reset_phone');

        if (!$userId || !$phone) {
            return redirect()->route('login')->withErrors([
                'password_reset_error' => 'Session expired. Please start again.',
            ]);
        }

        $user = User::findOrFail($userId);

        try {
            // Get OTP timeout from settings
            $otpTimeout = $this->getOtpTimeout();

            // Invalidate previous OTPs for password reset
            OtpCode::where('user_id', $user->id)
                ->where('purpose', 'password_reset')
                ->where('used', false)
                ->update(['used' => true, 'used_at' => now()]);

            // Generate 6-digit OTP
            $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store OTP in database
            $otp = OtpCode::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,
                'expires_at' => now()->addMinutes($otpTimeout),
                'purpose' => 'password_reset',
                'ip_address' => $request->ip(),
                'used' => false,
            ]);

            // Send OTP via SMS
            $message = "Your OfisiLink password reset OTP is: {$otpCode}. Valid for {$otpTimeout} minutes.";
            $smsSent = $this->notificationService->sendSMS($phone, $message);

            if (!$smsSent) {
                Log::warning('Password reset OTP created but SMS sending failed', [
                    'user_id' => $user->id,
                    'phone' => $phone,
                    'otp_code' => $otpCode
                ]);
            }

            // Send OTP via Email with professional template
            if ($user->email) {
                try {
                    $emailSubject = 'Password Reset OTP Verification - OfisiLink';
                    $emailBody = View::make('emails.otp', [
                        'otpCode' => $otpCode,
                        'userName' => $user->name,
                        'purpose' => 'password_reset',
                        'validityMinutes' => $otpTimeout,
                        'loginUrl' => route('login'),
                        'subject' => $emailSubject
                    ])->render();
                    
                    $emailSent = $this->notificationService->sendEmail(
                        $user->email,
                        $emailSubject,
                        $emailBody,
                        [
                            'type' => 'otp',
                            'purpose' => 'password_reset',
                            'otp_code' => $otpCode,
                            'is_html' => true  // Indicate this is already HTML
                        ]
                    );
                    
                    if ($emailSent) {
                        Log::info('Password reset OTP email sent successfully', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error sending password reset OTP email', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Mark that OTP has been sent
            session(['password_reset_otp_sent' => true]);

            // Check if request is AJAX/JSON
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP has been sent to your registered phone number. Please enter the code to reset your password.'
                ]);
            }

            return redirect()->route('login')->with([
                'password_reset_success' => 'OTP has been sent to your registered phone number. Please enter the code to reset your password.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending password reset OTP', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')->withErrors([
                'password_reset_error' => 'An error occurred. Please try again later.',
            ]);
        }
    }

    /**
     * Mask phone number - show only last 3 digits
     */
    private function maskPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Get last 3 digits
        $last3 = substr($cleanPhone, -3);
        
        // Get prefix (everything except last 3 digits)
        $prefix = substr($cleanPhone, 0, -3);
        
        // Mask the prefix with asterisks
        $maskedPrefix = str_repeat('*', strlen($prefix));
        
        // Reconstruct with original format if it had + or other characters
        if (strpos($phone, '+') === 0) {
            return '+' . $maskedPrefix . $last3;
        }
        
        return $maskedPrefix . $last3;
    }

    /**
     * Verify OTP and generate new password - Step 3
     */
    public function verifyPasswordResetOtp(Request $request)
    {
        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('password_reset_user_id');
        $sessionPhone = session('password_reset_phone');

        if (!$userId) {
            return redirect()->route('login')->withErrors([
                'password_reset_otp_error' => 'Session expired. Please request password reset again.',
            ]);
        }

        // Get user
        $user = User::findOrFail($userId);

        // Verify OTP
        $otp = OtpCode::where('user_id', $userId)
            ->where('purpose', 'password_reset')
            ->where('otp_code', $request->otp_code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return back()->withErrors([
                'password_reset_otp_error' => 'Invalid or expired OTP code.',
            ]);
        }

        // Generate new random password
        $newPassword = $this->generateRandomPassword();

        try {
            DB::beginTransaction();

            // Update user password
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            // Mark OTP as used
            $otp->update(['used' => true, 'used_at' => now()]);

            DB::commit();

            // Get phone number
            $userPhone = $user->mobile ?? $user->phone ?? $sessionPhone;

            // Send new password via SMS
            if ($userPhone) {
                $message = "Hello {$user->name},\n\nYour password has been reset successfully.\n\nYour new login credentials:\nEmail: {$user->email}\nPassword: {$newPassword}\n\nPlease login and change your password immediately.\n\nOfisiLink System";
                $smsSent = $this->notificationService->sendSMS($userPhone, $message);

                if ($smsSent) {
                    Log::info('Password reset SMS sent successfully', [
                        'user_id' => $user->id,
                        'phone' => $userPhone
                    ]);
                } else {
                    Log::warning('Password reset completed but SMS sending failed', [
                        'user_id' => $user->id,
                        'phone' => $userPhone
                    ]);
                }
            }

            // Send new password via Email with professional template
            if ($user->email) {
                try {
                    $emailSubject = 'Password Reset Successful - OfisiLink';
                    $emailBody = View::make('emails.password-reset', [
                        'userName' => $user->name,
                        'userEmail' => $user->email,
                        'newPassword' => $newPassword,
                        'loginUrl' => route('login'),
                        'subject' => $emailSubject,
                        'resetBy' => 'user' // User-initiated password reset
                    ])->render();
                    
                    $emailSent = $this->notificationService->sendEmail(
                        $user->email,
                        $emailSubject,
                        $emailBody,
                        [
                            'type' => 'password_reset',
                            'reset_by' => 'user',
                            'is_html' => true
                        ]
                    );
                    
                    if ($emailSent) {
                        Log::info('Password reset email sent successfully', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    } else {
                        Log::warning('Password reset completed but email sending failed', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error sending password reset email', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Log the password reset activity
            ActivityLogService::logPasswordReset($user, null, [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'password_reset_method' => 'forgot_password_otp',
                'password_length' => strlen($newPassword),
            ]);

            // Clear session
            session()->forget(['password_reset_user_id', 'password_reset_phone', 'password_reset_masked_phone', 'password_reset_otp_sent', 'password_reset_phone_confirm']);

            // Check if request is AJAX/JSON
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your password has been reset successfully. The new password has been sent to your registered phone number and email address. Please check your SMS and email, then login with the new password.'
                ]);
            }

            return redirect()->route('login')->with([
                'password_reset_complete' => true,
                'password_reset_success' => 'Your password has been reset successfully. The new password has been sent to your registered phone number and email address. Please check your SMS and email, then login with the new password.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resetting password', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'password_reset_otp_error' => 'An error occurred while resetting your password. Please try again.',
            ]);
        }
    }

    /**
     * Resend password reset OTP
     */
    public function resendPasswordResetOtp(Request $request)
    {
        $userId = session('password_reset_user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please request password reset again.',
            ], 422);
        }

        $user = User::findOrFail($userId);
        $phone = $user->mobile ?? $user->phone;

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number registered.',
            ], 422);
        }

        try {
            // Get OTP timeout from settings
            $otpTimeout = $this->getOtpTimeout();

            // Invalidate previous OTPs
            OtpCode::where('user_id', $user->id)
                ->where('purpose', 'password_reset')
                ->where('used', false)
                ->update(['used' => true, 'used_at' => now()]);

            // Generate new OTP
            $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store OTP
            OtpCode::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,
                'expires_at' => now()->addMinutes($otpTimeout),
                'purpose' => 'password_reset',
                'ip_address' => $request->ip(),
                'used' => false,
            ]);

            // Send OTP via SMS
            $message = "Your OfisiLink password reset OTP is: {$otpCode}. Valid for {$otpTimeout} minutes.";
            $smsSent = $this->notificationService->sendSMS($phone, $message);

            // Send OTP via Email with professional template
            $emailSent = false;
            if ($user->email) {
                try {
                    $emailSubject = 'Password Reset OTP Verification - OfisiLink (Resent)';
                    $emailBody = View::make('emails.otp', [
                        'otpCode' => $otpCode,
                        'userName' => $user->name,
                        'purpose' => 'password_reset',
                        'validityMinutes' => $otpTimeout,
                        'loginUrl' => route('login'),
                        'subject' => $emailSubject
                    ])->render();
                    
                    $emailSent = $this->notificationService->sendEmail(
                        $user->email,
                        $emailSubject,
                        $emailBody,
                        [
                            'type' => 'otp',
                            'purpose' => 'password_reset_resend',
                            'otp_code' => $otpCode,
                            'is_html' => true  // Indicate this is already HTML
                        ]
                    );
                } catch (\Exception $e) {
                    Log::warning('Error resending password reset OTP email', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($smsSent || $emailSent) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP has been resent to your registered phone number' . ($emailSent ? ' and email address' : '') . '.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error resending password reset OTP', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Generate a random secure password
     */
    private function generateRandomPassword($length = 12)
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghijkmnpqrstuvwxyz';
        $numbers = '23456789';
        $special = '!@#$%&*';
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        $all = $uppercase . $lowercase . $numbers . $special;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }
        
        return str_shuffle($password);
    }
}


