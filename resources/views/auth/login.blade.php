<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <meta name="description" content="Office Management System Login" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
      :root {
        --bs-primary: #940000 !important;
        --bs-primary-rgb: 148, 0, 0 !important;
        --bs-primary-hover: #a80000 !important;
      }
      
        * {
            box-sizing: border-box;
        }
        
        body {
            overflow-x: hidden;
        }
        
      .auth-wrapper {
            background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            width: 100%;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .auth-wrapper::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
            opacity: 0.3;
        }
        
        @keyframes float {
            0% { transform: translateY(0) translateX(0); }
            100% { transform: translateY(-100px) translateX(-100px); }
      }
      
      .auth-card {
        border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            animation: slideUp 0.6s ease-out;
            position: relative;
            z-index: 1;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2rem;
            animation: fadeInDown 0.8s ease-out;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container img {
            max-width: 200px;
            height: auto;
            animation: pulse 2s ease-in-out infinite;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 14px;
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 1;
            background: white;
            padding: 0 8px;
        }
        
        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group input.has-value + label {
            top: 0;
            transform: translateY(-50%);
            font-size: 12px;
            color: #940000;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 90px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #940000;
            box-shadow: 0 0 0 3px rgba(148, 0, 0, 0.1);
            padding-left: 95px;
        }
        
        .form-group .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 2;
        }
        
        .form-group input:focus ~ .input-icon {
            color: #940000;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 2;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
        color: #940000;
      }
      
        .btn-primary {
            background: linear-gradient(135deg, #940000 0%, #a80000 100%);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(148, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(148, 0, 0, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .form-check-label {
            color: #6c757d;
            cursor: pointer;
        }
        
        .forgot-password {
            color: #940000;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #a80000;
            text-decoration: underline;
        }
        
        .otp-container {
            display: none;
        }
        
        .otp-container.show {
            display: block;
        }
        
        /* Forgot password containers - Bootstrap d-none class handles visibility */
        /* No need for custom display rules, Bootstrap utilities will handle it */
        
        /* Phone Confirm Container */
        .phone-confirm-container.d-none {
            display: none !important;
        }
        
        .phone-confirm-container:not(.d-none) {
            display: block !important;
        }
        
        .phone-display-box {
            transition: all 0.3s ease;
        }
        
        .phone-display-box:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 20px rgba(148, 0, 0, 0.2);
        }
        
        /* Password Reset Progress Overlay */
        .password-reset-progress-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            z-index: 10000;
            display: none;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease-out;
        }
        
        .password-reset-progress-overlay.show {
            display: flex;
        }
        
        .password-reset-progress-content {
            text-align: center;
            max-width: 400px;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .login-container.hide {
            display: none;
        }
        
        .otp-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .otp-input:focus {
            outline: none;
            border-color: #940000;
            box-shadow: 0 0 0 3px rgba(148, 0, 0, 0.1);
            transform: scale(1.1);
        }
        
        .alert {
            border-radius: 10px;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .resend-otp {
            text-align: center;
            margin-top: 1rem;
            color: #6c757d;
        }
        
        .resend-otp a {
            color: #940000;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .resend-otp a:hover {
            color: #a80000;
            text-decoration: underline;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
      }
      
      /* Loading Splash Screen */
      .otp-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        animation: fadeIn 0.3s ease-out;
      }
      
      .otp-loading-overlay.show {
        display: flex;
      }
      
      .otp-loading-content {
        text-align: center;
        max-width: 400px;
        padding: 2rem;
      }
      
      .otp-loading-logo {
        width: 120px;
        height: auto;
        margin-bottom: 2rem;
        animation: pulse 2s ease-in-out infinite;
      }
      
      .otp-loading-title {
        font-size: 24px;
        font-weight: 600;
        color: #940000;
        margin-bottom: 1rem;
      }
      
      .otp-loading-subtitle {
        font-size: 16px;
        color: #6c757d;
        margin-bottom: 2rem;
      }
      
      .otp-progress-container {
        width: 100%;
        height: 8px;
        background: #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1rem;
        position: relative;
      }
      
      .otp-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #940000 0%, #a80000 50%, #940000 100%);
        background-size: 200% 100%;
        border-radius: 10px;
        width: 0%;
        transition: width 0.3s ease;
        animation: progressShimmer 2s linear infinite;
        position: relative;
      }
      
      @keyframes progressShimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
      }
      
      .otp-progress-text {
        font-size: 18px;
        font-weight: 600;
        color: #940000;
        margin-top: 0.5rem;
      }
      
      .otp-loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f0f0f0;
        border-top: 4px solid #940000;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 1rem auto;
      }
      
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    </style>
</head>

  <body>
    <div class="container-xx">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <div class="card auth-card">
                    <div class="card-body p-5">
              <!-- Logo -->
                        <div class="logo-container">
                            <img src="{{ asset('assets/img/office_link_logo.png') }}" alt="Logo" />
              </div>
            
                        <!-- Login Form -->
                        <div class="login-container" id="loginContainer">
                            @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
              @endif

                            <form id="formAuthentication" action="{{ route('login') }}" method="POST">
            @csrf
                                
                                <div class="form-group">
                                    <i class="bx bx-user input-icon"></i>
                  <input
                    type="text"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                                        placeholder=" "
                    autofocus
                    value="{{ old('email') }}"
                  />
                                    <label for="email">Username</label>
                  @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                                
                                <div class="form-group">
                                    <i class="bx bx-lock-alt input-icon"></i>
                    <input
                      type="password"
                      id="password"
                      class="form-control @error('password') is-invalid @enderror"
                      name="password"
                                        placeholder=" "
                    />
                                    <label for="password">Password</label>
                                    <span class="password-toggle" id="passwordToggle">
                                        <i class="bx bx-hide"></i>
                                    </span>
                    @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    </div>
                                
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                                        <label class="form-check-label" for="remember-me">Remember Me</label>
                    </div>
                                    <a href="#" class="forgot-password" id="forgotPasswordLink">Forgot Password?</a>
                </div>
                                
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    <span class="submit-text">Sign In</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
              </form>

                            <p class="text-center mt-3 mb-0">
                <span>New on our platform?</span>
                                <a href="https://ofisilink.com" target="_blank" class="forgot-password ms-1">Learn more</a>
                            </p>
                        </div>
                            
                        <!-- Forgot Password Form - Step 1: Enter Username/Email -->
                        <div class="forgot-password-container d-none" id="forgotPasswordContainer">
                            <h4 class="text-center mb-4" style="color: #940000; font-weight: 600;">Reset Password</h4>
                            <p class="text-center text-muted mb-4">Enter your email address or username</p>
                            
                            @if($errors->has('password_reset_username'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $errors->first('password_reset_username') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            <form id="forgotPasswordForm" method="POST" action="{{ route('password.forgot') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="password_reset_username" class="form-label">Email Address / Username</label>
                                    <input type="text" class="form-control @error('password_reset_username') is-invalid @enderror" 
                                           id="password_reset_username" name="username" 
                                           value="{{ old('password_reset_username') }}" 
                                           placeholder="Enter your email or username" required autofocus>
                                    <div class="form-text">Enter your registered email address or username</div>
                                    @error('password_reset_username')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    <span class="submit-text">Continue</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                                
                                <div class="text-center mt-3">
                                    <a href="#" class="forgot-password" id="backToLoginLink">Back to Login</a>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Phone Confirmation Step - Step 2: Confirm Phone Number -->
                        <div class="phone-confirm-container d-none" id="phoneConfirmContainer">
                            <h4 class="text-center mb-4" style="color: #940000; font-weight: 600;">Confirm Phone Number</h4>
                            <p class="text-center text-muted mb-4">Is this your phone number?</p>
                            
                            <div class="text-center mb-4">
                                <div class="phone-display-box p-4" style="background: #f8f9fa; border-radius: 10px; border: 2px solid #940000;">
                                    <h3 class="mb-0" id="maskedPhoneDisplay" style="color: #940000; font-weight: bold; font-size: 24px;">
                                        {{ session('password_reset_masked_phone', 'Loading...') }}
                                    </h3>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" type="button" id="confirmPhoneBtn">
                                    <span class="submit-text">Yes, Send OTP</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="wrongPhoneBtn">
                                    No, This is Wrong
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="#" class="forgot-password" id="backToUsernameLink">Back</a>
                            </div>
                        </div>
                        
                        <!-- Password Reset OTP Verification Form - Step 3: Verify OTP -->
                        <div class="password-reset-otp-container d-none" id="passwordResetOtpContainer">
                            <h4 class="text-center mb-4" style="color: #940000; font-weight: 600;">Enter OTP Code</h4>
                            <p class="text-center text-muted mb-4">We've sent a 6-digit code to your phone number</p>
                            
                            @if(session('password_reset_otp_error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('password_reset_otp_error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            @if(session('password_reset_success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('password_reset_success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            <form id="passwordResetOtpForm" method="POST" action="{{ route('password.verify.otp') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="password_reset_otp" class="form-label">OTP Code</label>
                                    <input type="text" class="form-control text-center" 
                                           id="password_reset_otp" name="otp_code" 
                                           maxlength="6" pattern="[0-9]{6}" required autofocus
                                           style="font-size: 24px; letter-spacing: 8px; font-weight: bold;">
                                    <div class="form-text text-center">Enter the 6-digit code sent to your phone</div>
                                </div>
                                
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    <span class="submit-text">Verify OTP</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                                
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-link text-decoration-none" id="resendPasswordResetOtp">
                                        Resend OTP
                                    </button>
                                </div>
                                
                                <div class="text-center mt-2">
                                    <a href="#" class="forgot-password" id="backToPhoneConfirmLink">Back</a>
                                </div>
                            </form>
                        </div>
                        
                        <!-- OTP Verification Form -->
                        <div class="otp-container" id="otpContainer">
                            <h4 class="text-center mb-4" style="color: #940000; font-weight: 600;">Enter Verification Code</h4>
                            <p class="text-center text-muted mb-4">We've sent a 6-digit code to your registered phone number</p>
                            
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
              @endif

                            <form id="otpForm" action="{{ route('login.otp.verify') }}" method="POST">
                                @csrf
                                
                                <div class="otp-input-group">
                                    <input type="text" class="otp-input" data-index="0" maxlength="1" pattern="[0-9]" required />
                                    <input type="text" class="otp-input" data-index="1" maxlength="1" pattern="[0-9]" required />
                                    <input type="text" class="otp-input" data-index="2" maxlength="1" pattern="[0-9]" required />
                                    <input type="text" class="otp-input" data-index="3" maxlength="1" pattern="[0-9]" required />
                                    <input type="text" class="otp-input" data-index="4" maxlength="1" pattern="[0-9]" required />
                                    <input type="text" class="otp-input" data-index="5" maxlength="1" pattern="[0-9]" required />
                                </div>
                                
                                <input type="hidden" name="otp_code" id="fullOtpCode" required />
                                
                                @error('otp_code')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                
                                <!-- Verify button removed - auto-verification when all 6 digits are entered -->
                            </form>
                            
                            <div class="resend-otp mt-3">
                                <p>Didn't receive the code? <a href="javascript:void(0)" id="resendOtpBtn">Resend OTP</a></p>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="{{ route('login') }}" class="forgot-password">Back to Login</a>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
    </div>
    
    <!-- OTP Loading Splash Screen -->
    <div class="otp-loading-overlay" id="otpLoadingOverlay">
        <div class="otp-loading-content">
            <img src="{{ asset('assets/img/office_link_logo.png') }}" alt="Logo" class="otp-loading-logo" />
            <h3 class="otp-loading-title">Verifying OTP</h3>
            <p class="otp-loading-subtitle">Please wait while we verify your code...</p>
            <div class="otp-progress-container">
                <div class="otp-progress-bar" id="otpProgressBar"></div>
            </div>
            <div class="otp-progress-text" id="otpProgressText">0%</div>
            <div class="otp-loading-spinner"></div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show OTP form if user is on OTP verification page
            @if(session('otp_user_id'))
                document.getElementById('loginContainer').classList.add('hide');
                document.getElementById('otpContainer').classList.add('show');
            @endif
            
            // Password toggle
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            
            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('bx-hide');
                    this.querySelector('i').classList.toggle('bx-show');
                });
            }
            
            // OTP Input handling
            const otpInputs = document.querySelectorAll('.otp-input');
            const fullOtpInput = document.getElementById('fullOtpCode');
            
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    // Auto-focus next input
                    if (this.value && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                    
                    updateFullOtp();
                });
                
                input.addEventListener('keydown', function(e) {
                    // Handle backspace
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });
                
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const numbers = paste.replace(/[^0-9]/g, '').split('').slice(0, 6);
                    
                    numbers.forEach((num, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = num;
                        }
                    });
                    
                    if (numbers.length === 6) {
                        otpInputs[5].focus();
                    } else if (numbers.length > 0) {
                        otpInputs[numbers.length].focus();
                    }
                    
                    updateFullOtp();
                });
            });
            
            // Make otpForm and related variables accessible globally for submitOtpForm
            const otpForm = document.getElementById('otpForm');
            const otpLoadingOverlay = document.getElementById('otpLoadingOverlay');
            const otpProgressBar = document.getElementById('otpProgressBar');
            const otpProgressText = document.getElementById('otpProgressText');
            let isSubmitting = false; // Flag to prevent multiple submissions
            
            function updateFullOtp() {
                const fullCode = Array.from(otpInputs).map(input => input.value).join('');
                if (fullOtpInput) {
                    fullOtpInput.value = fullCode;
                }
                
                // Auto-submit when all 6 digits are filled
                if (fullCode.length === 6 && otpForm && !isSubmitting) {
                    // Small delay to ensure the last digit is properly set
                    setTimeout(function() {
                        if (!isSubmitting) {
                            submitOtpForm();
                        }
                    }, 100);
                }
            }
            
            // Function to submit OTP form with loading splash
            function submitOtpForm() {
                if (isSubmitting) {
                    return false; // Prevent multiple submissions
                }
                
                isSubmitting = true;
                const fullCode = Array.from(otpInputs).map(input => input.value).join('');
                
                if (fullCode.length !== 6) {
                    isSubmitting = false;
                    return false;
                }
                
                if (fullOtpInput) {
                    fullOtpInput.value = fullCode;
                }
                
                // Disable OTP inputs during submission
                otpInputs.forEach(input => {
                    input.disabled = true;
                });
                
                // Show loading overlay
                if (otpLoadingOverlay) {
                    otpLoadingOverlay.classList.add('show');
                }
                
                // Reset progress
                if (otpProgressBar) {
                    otpProgressBar.style.width = '0%';
                }
                if (otpProgressText) {
                    otpProgressText.textContent = '0%';
                }
                
                // Animate progress from 0 to 100%
                let progress = 0;
                const progressInterval = setInterval(function() {
                    progress += 1.5; // Increment by 1.5% each interval for smoother animation
                    if (progress > 100) {
                        progress = 100;
                        clearInterval(progressInterval);
                    }
                    
                    if (otpProgressBar) {
                        otpProgressBar.style.width = progress + '%';
                    }
                    if (otpProgressText) {
                        otpProgressText.textContent = Math.round(progress) + '%';
                    }
                }, 40); // Update every 40ms for smooth animation (~2.7 seconds to 100%)
                
                // Ensure progress reaches 100% even if server responds quickly
                setTimeout(function() {
                    if (otpProgressBar && otpProgressBar.style.width !== '100%') {
                        otpProgressBar.style.width = '100%';
                    }
                    if (otpProgressText) {
                        otpProgressText.textContent = '100%';
                    }
                }, 3000); // Force 100% after 3 seconds
                
                // Store interval to clear if form submission fails
                window.otpProgressInterval = progressInterval;
                
                // Submit the form
                otpForm.submit();
            }
            
            // OTP Form submission handler (for manual submission if needed, though auto-submit is primary)
            if (otpForm) {
                otpForm.addEventListener('submit', function(e) {
                    const fullCode = Array.from(otpInputs).map(input => input.value).join('');
                    if (fullCode.length !== 6) {
                        e.preventDefault();
                        return false;
                    }
                    // Use the same submit function for consistency
                    e.preventDefault();
                    submitOtpForm();
                });
            }
            
            // Resend OTP
            const resendOtpBtn = document.getElementById('resendOtpBtn');
            if (resendOtpBtn) {
                resendOtpBtn.addEventListener('click', function() {
                    const btn = this;
                    const originalText = btn.textContent;
                    btn.textContent = 'Sending...';
                    btn.style.pointerEvents = 'none';
                    
                    fetch('{{ route("login.otp.resend") }}', {
            method: 'POST',
            headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            btn.textContent = originalText;
                        } else {
                            alert(data.message || 'Failed to resend OTP');
                            btn.textContent = originalText;
                        }
                        btn.style.pointerEvents = 'auto';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                        btn.textContent = originalText;
                        btn.style.pointerEvents = 'auto';
                    });
                });
            }
            
            // Form submission loading states
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const submitText = submitBtn.querySelector('.submit-text');
                        const spinner = submitBtn.querySelector('.spinner-border');
                        if (submitText) submitText.classList.add('d-none');
                        if (spinner) spinner.classList.remove('d-none');
                        submitBtn.disabled = true;
                    }
                });
            });
            
            // Add value class to inputs with existing values
            document.querySelectorAll('input').forEach(input => {
                if (input.value) { 
                    input.classList.add('has-value');
                }
                input.addEventListener('input', function() {
                    if (this.value) {
                        this.classList.add('has-value');
                    } else {
                        this.classList.remove('has-value');
                    }
                });
          });
          
          // Forgot Password UI Toggle
          const forgotPasswordLink = document.getElementById('forgotPasswordLink');
          const backToLoginLink = document.getElementById('backToLoginLink');
          const backToForgotPasswordLink = document.getElementById('backToPhoneConfirmLink');
          const backToUsernameLink = document.getElementById('backToUsernameLink');
          const forgotPasswordContainer = document.getElementById('forgotPasswordContainer');
          const phoneConfirmContainer = document.getElementById('phoneConfirmContainer');
          const passwordResetOtpContainer = document.getElementById('passwordResetOtpContainer');
          const loginContainer = document.getElementById('loginContainer');
          const otpContainer = document.getElementById('otpContainer');
          const passwordResetProgressOverlay = document.getElementById('passwordResetProgressOverlay');
          
          function showLoginForm() {
              if (loginContainer) loginContainer.classList.remove('d-none');
              if (otpContainer) {
                  otpContainer.classList.remove('show');
                  otpContainer.classList.add('d-none');
              }
              if (forgotPasswordContainer) forgotPasswordContainer.classList.add('d-none');
              if (phoneConfirmContainer) phoneConfirmContainer.classList.add('d-none');
              if (passwordResetOtpContainer) passwordResetOtpContainer.classList.add('d-none');
          }
          
          function showForgotPasswordForm() {
              if (loginContainer) loginContainer.classList.add('d-none');
              if (otpContainer) {
                  otpContainer.classList.remove('show');
                  otpContainer.classList.add('d-none');
              }
              if (forgotPasswordContainer) forgotPasswordContainer.classList.remove('d-none');
              if (phoneConfirmContainer) phoneConfirmContainer.classList.add('d-none');
              if (passwordResetOtpContainer) passwordResetOtpContainer.classList.add('d-none');
          }
          
          function showPhoneConfirmForm() {
              if (loginContainer) loginContainer.classList.add('d-none');
              if (otpContainer) {
                  otpContainer.classList.remove('show');
                  otpContainer.classList.add('d-none');
              }
              if (forgotPasswordContainer) forgotPasswordContainer.classList.add('d-none');
              if (phoneConfirmContainer) {
                  phoneConfirmContainer.classList.remove('d-none');
                  phoneConfirmContainer.style.display = 'block';
              }
              if (passwordResetOtpContainer) passwordResetOtpContainer.classList.add('d-none');
          }
          
          function showPasswordResetOtpForm() {
              if (loginContainer) loginContainer.classList.add('d-none');
              if (otpContainer) {
                  otpContainer.classList.remove('show');
                  otpContainer.classList.add('d-none');
              }
              if (forgotPasswordContainer) forgotPasswordContainer.classList.add('d-none');
              if (phoneConfirmContainer) phoneConfirmContainer.classList.add('d-none');
              if (passwordResetOtpContainer) passwordResetOtpContainer.classList.remove('d-none');
          }
          
          if (forgotPasswordLink) {
              forgotPasswordLink.addEventListener('click', function(e) {
                  e.preventDefault();
                  showForgotPasswordForm();
              });
          }
          
          if (backToLoginLink) {
              backToLoginLink.addEventListener('click', function(e) {
                  e.preventDefault();
                  showLoginForm();
              });
          }
          
          if (backToUsernameLink) {
              backToUsernameLink.addEventListener('click', function(e) {
                  e.preventDefault();
                  showForgotPasswordForm();
              });
          }
          
          if (backToForgotPasswordLink) {
              backToForgotPasswordLink.addEventListener('click', function(e) {
                  e.preventDefault();
                  showPhoneConfirmForm();
              });
          }
          
          // Handle phone confirmation
          const confirmPhoneBtn = document.getElementById('confirmPhoneBtn');
          const wrongPhoneBtn = document.getElementById('wrongPhoneBtn');
          
          if (confirmPhoneBtn) {
              confirmPhoneBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  
                  const submitText = this.querySelector('.submit-text');
                  const spinner = this.querySelector('.spinner-border');
                  
                  if (submitText) submitText.classList.add('d-none');
                  if (spinner) spinner.classList.remove('d-none');
                  this.disabled = true;
                  
                  fetch('{{ route("password.confirm.phone") }}', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'X-Requested-With': 'XMLHttpRequest',
                          'Accept': 'application/json'
                      }
                  })
                  .then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          showPasswordResetOtpForm();
                      } else {
                          alert(data.message || 'Failed to send OTP');
                      }
                      if (submitText) submitText.classList.remove('d-none');
                      if (spinner) spinner.classList.add('d-none');
                      this.disabled = false;
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert('An error occurred. Please try again.');
                      if (submitText) submitText.classList.remove('d-none');
                      if (spinner) spinner.classList.add('d-none');
                      this.disabled = false;
                  });
              });
          }
          
          if (wrongPhoneBtn) {
              wrongPhoneBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  showForgotPasswordForm();
              });
          }
          
          // Show phone confirm or OTP form based on session
          @if(session('password_reset_phone_confirm') || session('password_reset_masked_phone'))
              showPhoneConfirmForm();
              const maskedPhoneDisplay = document.getElementById('maskedPhoneDisplay');
              if (maskedPhoneDisplay) {
                  maskedPhoneDisplay.textContent = '{{ session("password_reset_masked_phone", "Loading...") }}';
              }
          @elseif(session('password_reset_otp_sent') || session('password_reset_success'))
              showPasswordResetOtpForm();
          @endif
          
          // Handle OTP verification with progress bar
          const passwordResetOtpForm = document.getElementById('passwordResetOtpForm');
          if (passwordResetOtpForm) {
              passwordResetOtpForm.addEventListener('submit', function(e) {
                  e.preventDefault();
                  
                  // Show progress overlay
                  if (passwordResetProgressOverlay) {
                      passwordResetProgressOverlay.classList.add('show');
                  }
                  
                  // Start progress animation
                  let progress = 0;
                  const progressBar = document.getElementById('passwordResetProgressBar');
                  const progressText = document.getElementById('passwordResetProgressText');
                  const statusText = document.getElementById('passwordResetStatusText');
                  
                  const progressInterval = setInterval(() => {
                      progress += 2;
                      if (progress > 90) progress = 90; // Don't go to 100% until verification is complete
                      
                      if (progressBar) {
                          progressBar.style.width = progress + '%';
                          progressBar.setAttribute('aria-valuenow', progress);
                      }
                      if (progressText) {
                          progressText.textContent = Math.round(progress) + '%';
                      }
                      
                      // Update status text
                      if (statusText) {
                          if (progress < 30) {
                              statusText.textContent = 'Verifying OTP code...';
                          } else if (progress < 60) {
                              statusText.textContent = 'Generating new password...';
                          } else if (progress < 90) {
                              statusText.textContent = 'Sending password to your phone...';
                          }
                      }
                  }, 100);
                  
                  // Submit form via AJAX
                  const formData = new FormData(this);
                  
                  fetch(this.action, {
                      method: 'POST',
                      body: formData,
                      headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'Accept': 'application/json'
                      }
                  })
                  .then(response => {
                      const contentType = response.headers.get('content-type');
                      if (contentType && contentType.includes('application/json')) {
                          return response.json();
                      } else {
                          // If redirect, follow it
                          return response.text().then(html => {
                              return { redirect: true, html: html };
                          });
                      }
                  })
                  .then(data => {
                      clearInterval(progressInterval);
                      
                      // Complete progress
                      if (progressBar) {
                          progressBar.style.width = '100%';
                          progressBar.setAttribute('aria-valuenow', 100);
                      }
                      if (progressText) {
                          progressText.textContent = '100%';
                      }
                      if (statusText) {
                          statusText.textContent = 'Password reset successful!';
                      }
                      
                      setTimeout(() => {
                          if (passwordResetProgressOverlay) {
                              passwordResetProgressOverlay.classList.remove('show');
                          }
                          
                          if (data && data.redirect) {
                              // Reload page to show success message
                              window.location.reload();
                          } else if (data && data.success) {
                              alert('Password reset successful! Your new password has been sent to your phone number. Please check your SMS and login with the new password.');
                              window.location.href = '{{ route("login") }}';
                          } else if (data && !data.success) {
                              alert(data.message || 'An error occurred. Please try again.');
                          } else {
                              // If not JSON, it might be a redirect - reload page
                              window.location.reload();
                          }
                      }, 1000);
                  })
                  .catch(error => {
                      clearInterval(progressInterval);
                      
                      if (passwordResetProgressOverlay) {
                          passwordResetProgressOverlay.classList.remove('show');
                      }
                      
                      console.error('Error:', error);
                      // If not JSON, it might be a redirect - let form submit normally
                      this.submit();
                  });
              });
          }
          
          // Resend Password Reset OTP
          const resendPasswordResetOtp = document.getElementById('resendPasswordResetOtp');
          if (resendPasswordResetOtp) {
              resendPasswordResetOtp.addEventListener('click', function(e) {
                  e.preventDefault();
                  const btn = this;
                  const originalText = btn.textContent;
                  btn.textContent = 'Sending...';
                  btn.disabled = true;
                  
                  fetch('{{ route("password.resend.otp") }}', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      }
                  })
                  .then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          alert(data.message);
                      } else {
                          alert(data.message || 'Failed to resend OTP');
                      }
                      btn.textContent = originalText;
                      btn.disabled = false;
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert('An error occurred. Please try again.');
                      btn.textContent = originalText;
                      btn.disabled = false;
                  });
              });
          }
        });
    </script>
    
    <!-- Password Reset Progress Splash Screen -->
    <div class="password-reset-progress-overlay" id="passwordResetProgressOverlay">
        <div class="password-reset-progress-content">
            <div class="text-center">
                <img src="{{ asset('assets/img/office_link_logo.png') }}" alt="Logo" class="otp-loading-logo" style="width: 120px; height: auto; margin-bottom: 2rem;" />
                <h4 class="mb-3" style="color: #940000;">Verifying OTP...</h4>
                <p class="text-muted mb-3">Please wait while we verify your code</p>
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         id="passwordResetProgressBar"
                         style="width: 0%; background-color: #940000;"
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        <span id="passwordResetProgressText" style="line-height: 25px; font-weight: bold;">0%</span>
                    </div>
                </div>
                <p class="text-muted small" id="passwordResetStatusText">Initializing verification...</p>
            </div>
        </div>
    </div>
</body>
</html>

