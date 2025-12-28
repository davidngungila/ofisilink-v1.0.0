<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ImprestController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AccountsPayableController;
use App\Http\Controllers\AccountsReceivableController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetingController;
use App\Http\Controllers\CashBankController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\MeetingController;

// Helper function to get subdomain
if (!function_exists('getSubdomain')) {
    function getSubdomain() {
        $host = request()->getHost();
        $parts = explode('.', $host);
        
        // For localhost or IP, return null
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }
        
        // If more than 2 parts, first is subdomain
        if (count($parts) > 2) {
            return $parts[0];
        }
        
        return null;
    }
}

/**
 * Domain Structure:
 * - Main Domain (ofisilink.com): Landing page
 * - Live Subdomain (live.ofisilink.com): Production system - redirects to login
 * - Demo Subdomain (demo.ofisilink.com): Demo system - redirects to login
 */

// Root route - always redirect to login page
Route::get('/', function () {
    return redirect()->route('login');
})->name('landing');

// Public Careers/Jobs Page
Route::get('/careers', [App\Http\Controllers\RecruitmentController::class, 'publicCareers'])->name('careers');
Route::view('/landing', 'public.landing')->name('public.landing');
Route::get('/api/jobs/public', [App\Http\Controllers\RecruitmentController::class, 'getPublicJobs'])->name('api.jobs.public');
Route::post('/api/jobs/apply', [App\Http\Controllers\RecruitmentController::class, 'submitPublicApplication'])->name('api.jobs.apply');

// Serve storage photos - MUST be before auth middleware
Route::get('/storage/photos/{filename}', [\App\Http\Controllers\AccountSettingsController::class, 'servePhoto'])
    ->where('filename', '[^/]+\.(jpg|jpeg|png|gif|webp)')
    ->name('storage.photos');


// Test route to check infinite loading
Route::get('/test-dashboard', function () {
    return view('test-dashboard');
})->name('test-dashboard')->middleware('auth');




// Authentication
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/otp-only', [AuthController::class, 'loginWithOtp'])->name('login.otp-only');
Route::get('/login/otp', [AuthController::class, 'showOtpForm'])->name('login.otp');
Route::get('/login/otp/verify', [AuthController::class, 'redirectOtpVerify'])->name('login.otp.verify.get');
Route::post('/login/otp/verify', [AuthController::class, 'verifyOtp'])->name('login.otp.verify');
Route::post('/login/otp/resend', [AuthController::class, 'resendOtp'])->name('login.otp.resend');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// Forgot Password Routes
Route::get('/password/forgot', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot');
Route::post('/password/forgot', [AuthController::class, 'requestPasswordReset'])->name('password.forgot');
Route::post('/password/confirm-phone', [AuthController::class, 'confirmPhoneAndSendOtp'])->name('password.confirm.phone');
Route::post('/password/verify-otp', [AuthController::class, 'verifyPasswordResetOtp'])->name('password.verify.otp');
Route::post('/password/resend-otp', [AuthController::class, 'resendPasswordResetOtp'])->name('password.resend.otp');





// Protected routes
Route::middleware(['auth'])->group(function () {
    // Main dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Notifications API
    Route::get('/notifications/unread', [DashboardController::class, 'getUnreadNotifications'])->name('notifications.unread');
    
    // Role-specific dashboard routes
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard')->middleware('role:System Admin');
    Route::get('/ceo/dashboard', [DashboardController::class, 'ceoDashboard'])->name('ceo.dashboard')->middleware('role:CEO,Director');
    Route::get('/hod/dashboard', [DashboardController::class, 'hodDashboard'])->name('hod.dashboard')->middleware('role:HOD');
    Route::get('/accountant/dashboard', [DashboardController::class, 'accountantDashboard'])->name('accountant.dashboard')->middleware('role:Accountant');
    Route::get('/hr/dashboard', [DashboardController::class, 'hrDashboard'])->name('hr.dashboard')->middleware('role:HR Officer');
    Route::get('/staff/dashboard', [DashboardController::class, 'staffDashboard'])->name('staff.dashboard')->middleware('role:Staff');
    
    // Finance Module Routes
    Route::get('/modules/finance/petty', [PettyCashController::class, 'index'])->name('modules.finance.petty');
    // CEO view alias for petty cash pending (to match references in CEO dashboard)
    Route::get('/modules/finance/petty/ceo', [PettyCashController::class, 'ceoIndex'])
        ->name('modules.finance.petty-ceo')
        ->middleware('role:CEO,Director');
    // Imprest Management Routes
    Route::prefix('imprest')->name('imprest.')->group(function () {
        // Dashboard
        Route::get('/', [ImprestController::class, 'index'])->name('index');
        
        // Separate Pages
        Route::get('/create', [ImprestController::class, 'create'])->name('create');
        Route::get('/pending-hod', [ImprestController::class, 'pendingHod'])->name('pending-hod');
        Route::get('/pending-ceo', [ImprestController::class, 'pendingCeo'])->name('pending-ceo');
        Route::get('/approved', [ImprestController::class, 'approved'])->name('approved');
        Route::get('/assigned', [ImprestController::class, 'assigned'])->name('assigned');
        Route::get('/paid', [ImprestController::class, 'paid'])->name('paid');
        Route::get('/pending-verification', [ImprestController::class, 'pendingVerification'])->name('pending-verification');
        Route::get('/completed', [ImprestController::class, 'completed'])->name('completed');
        Route::get('/my-assignments', [ImprestController::class, 'myAssignments'])->name('my-assignments');
        
        // Actions
        Route::post('/', [ImprestController::class, 'store'])->name('store');
        Route::post('/assign-staff', [ImprestController::class, 'assignStaff'])->name('assign-staff');
        
        // Assignment routes (must be before generic {id} routes)
        Route::get('/assignment/{assignmentId}/submit-receipt', [ImprestController::class, 'submitReceiptPage'])->name('submit-receipt.page')->where('assignmentId', '[0-9]+');
        Route::get('/assignment/{assignmentId}/receipts', [ImprestController::class, 'viewMyReceiptsPage'])->name('my-receipts.page')->where('assignmentId', '[0-9]+');
        Route::get('/assignment/{assignmentId}/view-receipts', [ImprestController::class, 'viewReceiptsPage'])->name('view-receipts.page')->where('assignmentId', '[0-9]+');
        Route::get('/assignment/{id}', [ImprestController::class, 'getAssignment'])->name('assignment.get')->where('id', '[0-9]+');
        
        // Receipt routes (must be before generic {id} routes)
        Route::post('/receipts/{receiptId}/verify', [ImprestController::class, 'verifyReceipt'])->name('receipts.verify')->where('receiptId', '[0-9]+');
        Route::post('/receipts/bulk-verify', [ImprestController::class, 'bulkVerifyReceipts'])->name('receipts.bulk-verify');
        Route::get('/receipts/{receiptId}/details', [ImprestController::class, 'getReceiptDetails'])->name('receipts.details')->where('receiptId', '[0-9]+');
        
        // Action routes
        Route::post('/submit-receipt', [ImprestController::class, 'submitReceipt'])->name('submit-receipt');
        Route::post('/bulk-payment', [ImprestController::class, 'bulkPayment'])->name('bulk-payment');
        Route::post('/{id}/assignment/{assignmentId}/payment', [ImprestController::class, 'processIndividualPayment'])->name('individual-payment')->where('id', '[0-9]+')->where('assignmentId', '[0-9]+');
        Route::get('/verification', [ImprestController::class, 'verificationPage'])->name('verification');
        Route::get('/export/pdf', [ImprestController::class, 'exportPDF'])->name('export-pdf');
        
        // Generic {id} routes (must be last to avoid conflicts)
        Route::get('/{id}/assign-staff', [ImprestController::class, 'assignStaffPage'])->name('assign-staff.page')->where('id', '[0-9]+');
        Route::get('/{id}/payment', [ImprestController::class, 'paymentPage'])->name('payment.page')->where('id', '[0-9]+');
        Route::post('/{id}/payment', [ImprestController::class, 'processPayment'])->name('payment')->where('id', '[0-9]+');
        Route::post('/{id}/hod-approve', [ImprestController::class, 'hodApprove'])->name('hod-approve')->where('id', '[0-9]+');
        Route::post('/{id}/ceo-approve', [ImprestController::class, 'ceoApprove'])->name('ceo-approve')->where('id', '[0-9]+');
        Route::get('/{id}/pdf', [ImprestController::class, 'generatePDF'])->name('pdf')->where('id', '[0-9]+');
        Route::get('/{id}', [ImprestController::class, 'show'])->name('show')->where('id', '[0-9]+');
    });
    Route::view('/modules/finance/ledger', 'modules.finance.ledger')->name('modules.finance.ledger');
    Route::post('/modules/finance/ledger/data', [\App\Http\Controllers\FinanceLedgerController::class, 'data'])->name('modules.finance.ledger.data');
    Route::get('/modules/finance/ledger/pdf', [\App\Http\Controllers\FinanceLedgerController::class, 'exportPdf'])->name('modules.finance.ledger.pdf');
    
    // Notifications
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationsController::class, 'unread'])->name('notifications.unread');
    Route::get('/notifications/dropdown', [\App\Http\Controllers\NotificationsController::class, 'dropdown'])->name('notifications.dropdown');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationsController::class, 'markRead'])->name('notifications.read');

    // Meeting Management Routes
    Route::prefix('modules/meetings')->name('modules.meetings.')->group(function () {
        Route::get('/', [MeetingController::class, 'index'])->name('index');
        Route::post('/ajax', [MeetingController::class, 'ajax'])->name('ajax');
        Route::get('/minutes', [MeetingController::class, 'minutes'])->name('minutes.index');
        Route::get('/pending/minutes', [MeetingController::class, 'pendingMinutes'])->name('minutes.pending');
        Route::get('/{meeting}/agendas', [MeetingController::class, 'agendas'])->name('agendas');
        Route::get('/{meeting}/previous-actions', [MeetingController::class, 'previousActions'])->name('previous-actions');
        Route::post('/categories', [MeetingController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [MeetingController::class, 'updateCategory'])->name('categories.update');
        Route::post('/', [MeetingController::class, 'store'])->name('store');
        Route::put('/{meeting}', [MeetingController::class, 'update'])->name('update');
        Route::post('/{meeting}/submit', [MeetingController::class, 'submitForApproval'])->name('submit');
        Route::post('/{meeting}/approve', [MeetingController::class, 'approve'])->name('approve');
        Route::post('/{meeting}/reject', [MeetingController::class, 'reject'])->name('reject');
        // Minutes creation uses meeting_id from form payload, no route param required
        Route::post('/minutes', [MeetingController::class, 'storeMinutes'])->name('minutes.store');
    });

    // Account Settings
    Route::prefix('account/settings')->name('account.settings.')->group(function(){
        Route::get('/', [\App\Http\Controllers\AccountSettingsController::class, 'index'])->name('index');
        Route::post('/profile', [\App\Http\Controllers\AccountSettingsController::class, 'updateProfile'])->name('profile');
        Route::post('/photo', [\App\Http\Controllers\AccountSettingsController::class, 'updatePhoto'])->name('photo');
        Route::post('/phone/otp/send', [\App\Http\Controllers\AccountSettingsController::class, 'sendPhoneOtp'])->name('phone.otp.send');
        Route::post('/phone/otp/verify', [\App\Http\Controllers\AccountSettingsController::class, 'verifyPhoneOtp'])->name('phone.otp.verify');
        Route::post('/phone', [\App\Http\Controllers\AccountSettingsController::class, 'updatePhone'])->name('phone');
        Route::post('/password/otp/send', [\App\Http\Controllers\AccountSettingsController::class, 'sendPasswordOtp'])->name('password.otp.send');
        Route::post('/password/otp/verify', [\App\Http\Controllers\AccountSettingsController::class, 'verifyPasswordOtp'])->name('password.otp.verify');
        Route::post('/password/update', [\App\Http\Controllers\AccountSettingsController::class, 'updatePassword'])->name('password.update');
    });
    
    // Petty Cash Workflow Routes
    Route::prefix('petty-cash')->name('petty-cash.')->group(function () {
        // Dashboard
        Route::get('/', [PettyCashController::class, 'index'])->name('index');
        
        // Separate Pages
        Route::get('/all', [PettyCashController::class, 'all'])->name('all');
        Route::get('/create', [PettyCashController::class, 'create'])->name('create');
        Route::get('/pending-accountant', [PettyCashController::class, 'pendingAccountant'])->name('pending-accountant');
        Route::get('/accountant', [PettyCashController::class, 'accountantIndex'])->name('accountant.index')->middleware('role:Accountant,System Admin');
        Route::get('/pending-hod', [PettyCashController::class, 'pendingHod'])->name('pending-hod');
        Route::get('/hod', [PettyCashController::class, 'hodIndex'])->name('hod.index')->middleware('role:HOD,System Admin');
        Route::get('/pending-ceo', [PettyCashController::class, 'pendingCeo'])->name('pending-ceo');
        Route::get('/ceo', [PettyCashController::class, 'ceoIndex'])->name('ceo.index')->middleware('role:CEO,Director,System Admin');
        Route::get('/approved', [PettyCashController::class, 'approved'])->name('approved');
        Route::get('/paid', [PettyCashController::class, 'paid'])->name('paid');
        Route::get('/pending-retirement', [PettyCashController::class, 'pendingRetirement'])->name('pending-retirement');
        Route::get('/retired', [PettyCashController::class, 'retired'])->name('retired');
        Route::get('/my-requests', [PettyCashController::class, 'myRequests'])->name('my-requests');
        
        // Actions
        Route::post('/', [PettyCashController::class, 'store'])->name('store');
        
        // Direct Vouchers Management (Accountant and HOD) - MUST BE BEFORE /{pettyCash} route
        Route::get('/direct-vouchers', [PettyCashController::class, 'directVouchersIndex'])->name('direct-vouchers.index')->middleware('role:Accountant,HOD,System Admin');
        
        // Action routes
        Route::post('/{pettyCash}/accountant-verify', [PettyCashController::class, 'accountantVerify'])->name('accountant.verify')->middleware('role:Accountant')->where('pettyCash', '[0-9]+');
        Route::post('/accountant/direct-voucher', [PettyCashController::class, 'storeDirectVoucher'])->name('accountant.direct-voucher')->middleware('role:Accountant,System Admin');
        Route::post('/{pettyCash}/hod-approve', [PettyCashController::class, 'hodApprove'])->name('hod.approve')->middleware('role:HOD,System Admin')->where('pettyCash', '[0-9]+');
        Route::post('/{pettyCash}/ceo-approve', [PettyCashController::class, 'ceoApprove'])->name('ceo.approve')->middleware('role:CEO,Director')->where('pettyCash', '[0-9]+');
        Route::post('/{pettyCash}/mark-paid', [PettyCashController::class, 'markPaid'])->name('payment.mark-paid')->middleware('role:Accountant')->where('pettyCash', '[0-9]+');
        Route::post('/{pettyCash}/submit-retirement', [PettyCashController::class, 'submitRetirement'])->name('retirement.submit')->where('pettyCash', '[0-9]+');
        Route::post('/{pettyCash}/approve-retirement', [PettyCashController::class, 'approveRetirement'])->name('retirement.approve')->middleware('role:Accountant,System Admin')->where('pettyCash', '[0-9]+');
        
        // Generic {id} routes (must be last to avoid conflicts)
        Route::get('/{pettyCash}/details-ajax', [PettyCashController::class, 'details'])->name('details.ajax')->where('pettyCash', '[0-9]+');
        Route::get('/{pettyCash}/pdf', [PettyCashController::class, 'generatePdf'])->name('pdf')->where('pettyCash', '[0-9]+');
        Route::get('/{pettyCash}', [PettyCashController::class, 'show'])->name('show')->where('pettyCash', '[0-9]+');
        Route::delete('/{pettyCash}', [PettyCashController::class, 'destroy'])->name('destroy')->where('pettyCash', '[0-9]+');
    });

    // Finance Settings (Accountant/System Admin)
    Route::prefix('finance/settings')->name('finance.settings.')->middleware('role:Accountant,System Admin')->group(function(){
        Route::get('/', [\App\Http\Controllers\FinanceSetupController::class, 'index'])->name('index');
        Route::post('/gl', [\App\Http\Controllers\FinanceSetupController::class, 'storeGl'])->name('gl.store');
        Route::post('/gl/{gl}', [\App\Http\Controllers\FinanceSetupController::class, 'updateGl'])->name('gl.update');
        Route::post('/gl/{gl}/delete', [\App\Http\Controllers\FinanceSetupController::class, 'destroyGl'])->name('gl.destroy');

        Route::post('/cb', [\App\Http\Controllers\FinanceSetupController::class, 'storeCashBox'])->name('cb.store');
        Route::post('/cb/{cashBox}', [\App\Http\Controllers\FinanceSetupController::class, 'updateCashBox'])->name('cb.update');
        Route::post('/cb/{cashBox}/delete', [\App\Http\Controllers\FinanceSetupController::class, 'destroyCashBox'])->name('cb.destroy');
        
        Route::post('/sync-all', [\App\Http\Controllers\FinanceSetupController::class, 'syncAll'])->name('sync-all');
    });
    
    // File Management Routes - Digital Files
    Route::get('/modules/files/digital', [App\Http\Controllers\DigitalFileController::class, 'index'])->name('modules.files.digital');
    Route::prefix('modules/files/digital')->name('modules.files.digital.')->group(function(){
        Route::get('/dashboard', [App\Http\Controllers\DigitalFileController::class, 'dashboard'])->name('dashboard');
        Route::get('/folder/{folder}', [App\Http\Controllers\DigitalFileController::class, 'folderDetail'])->name('folder.detail');
        Route::get('/upload', [App\Http\Controllers\DigitalFileController::class, 'upload'])->name('upload');
        Route::get('/manage', [App\Http\Controllers\DigitalFileController::class, 'manage'])->name('manage');
        Route::get('/search', [App\Http\Controllers\DigitalFileController::class, 'search'])->name('search');
        Route::get('/analytics', [App\Http\Controllers\DigitalFileController::class, 'analytics'])->name('analytics');
        Route::get('/access-requests', [App\Http\Controllers\DigitalFileController::class, 'accessRequests'])->name('access-requests');
        Route::get('/activity-log', [App\Http\Controllers\DigitalFileController::class, 'activityLog'])->name('activity-log');
        Route::get('/settings', [App\Http\Controllers\DigitalFileController::class, 'settings'])->name('settings');
        Route::get('/assign', [App\Http\Controllers\DigitalFileController::class, 'assign'])->name('assign');
        Route::get('/download-folder-template', [App\Http\Controllers\DigitalFileController::class, 'downloadFolderTemplate'])->name('download-folder-template');
        Route::post('/ajax', [App\Http\Controllers\DigitalFileController::class, 'handleRequest'])->name('ajax');
    });
    
    // Physical Files Routes
    Route::get('/modules/files/physical', [App\Http\Controllers\PhysicalRackController::class, 'index'])->name('modules.files.physical');
    Route::get('/modules/files/physical/download-rack-template', [App\Http\Controllers\PhysicalRackController::class, 'downloadRackTemplate'])->name('modules.files.physical.download-rack-template');
    Route::prefix('modules/files/physical')->name('modules.files.physical.')->group(function(){
        Route::get('/dashboard', [App\Http\Controllers\PhysicalRackController::class, 'dashboard'])->name('dashboard');
        Route::get('/rack/{rack}', [App\Http\Controllers\PhysicalRackController::class, 'rackDetail'])->name('rack.detail');
        Route::get('/upload', [App\Http\Controllers\PhysicalRackController::class, 'upload'])->name('upload');
        Route::get('/manage', [App\Http\Controllers\PhysicalRackController::class, 'manage'])->name('manage');
        Route::get('/search', [App\Http\Controllers\PhysicalRackController::class, 'search'])->name('search');
        Route::get('/analytics', [App\Http\Controllers\PhysicalRackController::class, 'analytics'])->name('analytics');
        Route::get('/access-requests', [App\Http\Controllers\PhysicalRackController::class, 'accessRequests'])->name('access-requests');
        Route::get('/activity-log', [App\Http\Controllers\PhysicalRackController::class, 'activityLog'])->name('activity-log');
        Route::get('/settings', [App\Http\Controllers\PhysicalRackController::class, 'settings'])->name('settings');
        Route::get('/assign', [App\Http\Controllers\PhysicalRackController::class, 'assign'])->name('assign');
        Route::post('/ajax', [App\Http\Controllers\PhysicalRackController::class, 'handleRequest'])->name('ajax');
    });
    
    // Asset Management (HR or Accountant)
    Route::prefix('modules/assets')->middleware('role:HR Officer,Accountant,System Admin')->group(function(){
        Route::get('/', [\App\Http\Controllers\AssetManagementController::class, 'index'])->name('modules.assets');
        
        // Categories
        Route::get('/categories', [\App\Http\Controllers\AssetManagementController::class, 'listCategories'])->name('assets.categories.list');
        Route::post('/categories', [\App\Http\Controllers\AssetManagementController::class, 'storeCategory'])->name('assets.categories.store');
        Route::post('/categories/{category}', [\App\Http\Controllers\AssetManagementController::class, 'updateCategory'])->name('assets.categories.update');
        Route::post('/categories/{category}/delete', [\App\Http\Controllers\AssetManagementController::class, 'destroyCategory'])->name('assets.categories.destroy');
        
        // Assets
        Route::get('/items', [\App\Http\Controllers\AssetManagementController::class, 'listAssets'])->name('assets.items.list');
        Route::get('/items/{asset}', [\App\Http\Controllers\AssetManagementController::class, 'getAsset'])->name('assets.items.show');
        Route::post('/items', [\App\Http\Controllers\AssetManagementController::class, 'storeAsset'])->name('assets.items.store');
        Route::post('/items/{asset}', [\App\Http\Controllers\AssetManagementController::class, 'updateAsset'])->name('assets.items.update');
        Route::post('/items/{asset}/delete', [\App\Http\Controllers\AssetManagementController::class, 'destroyAsset'])->name('assets.items.destroy');
        Route::post('/items/{asset}/assign', [\App\Http\Controllers\AssetManagementController::class, 'assignAsset'])->name('assets.items.assign');
        Route::post('/items/{asset}/return', [\App\Http\Controllers\AssetManagementController::class, 'returnAsset'])->name('assets.items.return');
        
        // Assignments
        Route::get('/assignments', [\App\Http\Controllers\AssetManagementController::class, 'listAssignments'])->name('assets.assignments.list');
        
        // Issues
        Route::get('/issues', [\App\Http\Controllers\AssetManagementController::class, 'listIssues'])->name('assets.issues.list');
        Route::get('/issues/statistics', [\App\Http\Controllers\AssetManagementController::class, 'getIssueStatistics'])->name('assets.issues.statistics');
        Route::get('/issues/{issue}', [\App\Http\Controllers\AssetManagementController::class, 'getIssue'])->name('assets.issues.show');
        Route::get('/issues/{issue}/history', [\App\Http\Controllers\AssetManagementController::class, 'getIssueHistory'])->name('assets.issues.history');
        Route::post('/items/{asset}/issues', [\App\Http\Controllers\AssetManagementController::class, 'reportIssue'])->name('assets.issues.store');
        Route::post('/issues/{issue}', [\App\Http\Controllers\AssetManagementController::class, 'updateIssue'])->name('assets.issues.update');
        Route::post('/issues/bulk-update', [\App\Http\Controllers\AssetManagementController::class, 'bulkUpdateIssues'])->name('assets.issues.bulk-update');
        Route::get('/issues/export', [\App\Http\Controllers\AssetManagementController::class, 'exportIssues'])->name('assets.issues.export');
        
        // Maintenance
        Route::get('/maintenance', [\App\Http\Controllers\AssetManagementController::class, 'listMaintenance'])->name('assets.maintenance.list');
        Route::get('/maintenance/{maintenance}', [\App\Http\Controllers\AssetManagementController::class, 'getMaintenance'])->name('assets.maintenance.show');
        Route::post('/items/{asset}/maintenance', [\App\Http\Controllers\AssetManagementController::class, 'scheduleMaintenance'])->name('assets.maintenance.store');
        Route::post('/maintenance/{maintenance}', [\App\Http\Controllers\AssetManagementController::class, 'updateMaintenance'])->name('assets.maintenance.update');
    });
    
    // Task Management Routes
    Route::get('/modules/tasks', [App\Http\Controllers\TaskController::class, 'index'])->name('modules.tasks');
    Route::post('/modules/tasks/action', [App\Http\Controllers\TaskController::class, 'action'])->name('modules.tasks.action');
    Route::get('/modules/tasks/pdf', [App\Http\Controllers\TaskController::class, 'generatePdf'])->name('modules.tasks.pdf');
    Route::get('/modules/tasks/analytics-pdf', [App\Http\Controllers\TaskController::class, 'analyticsPdf'])->name('modules.tasks.analytics.pdf');
    
    // Assessments Module Routes
    Route::get('/modules/assessments', [App\Http\Controllers\AssessmentController::class, 'index'])->name('modules.assessments');
    Route::post('/modules/assessments/action', [App\Http\Controllers\AssessmentController::class, 'action'])->name('modules.assessments.action');
    Route::get('/modules/assessments/pdf', [App\Http\Controllers\AssessmentController::class, 'pdf'])->name('modules.assessments.pdf');
    
// HR Module Routes (authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/modules/hr/leave', [LeaveController::class, 'index'])->name('modules.hr.leave');
    Route::get('/modules/hr/leave/new', [LeaveController::class, 'newRequest'])->name('modules.hr.leave.new');
    Route::get('/modules/hr/leave/balance', [LeaveController::class, 'balanceManagement'])->name('modules.hr.leave.balance');
    Route::get('/modules/hr/leave/recommendations', [LeaveController::class, 'recommendationsManagement'])->name('modules.hr.leave.recommendations');
    Route::get('/modules/hr/leave/analytics', [LeaveController::class, 'analyticsPage'])->name('modules.hr.leave.analytics');
    Route::get('/modules/hr/permissions', [PermissionController::class, 'index'])->name('modules.hr.permissions');
    Route::get('/modules/hr/employees', [EmployeeController::class, 'index'])->name('modules.hr.employees');
    Route::get('/modules/hr/employees/register', [EmployeeController::class, 'create'])->name('modules.hr.employees.register');
    Route::post('/modules/hr/employees/register', [EmployeeController::class, 'store'])->name('modules.hr.employees.store');
    Route::get('/modules/hr/employees/{userId}/review', [EmployeeController::class, 'review'])->name('modules.hr.employees.review');
    Route::post('/modules/hr/employees/{userId}/finalize', [EmployeeController::class, 'finalize'])->name('modules.hr.employees.finalize');
    Route::get('/modules/hr/employees/{userId}/registration-pdf', [EmployeeController::class, 'generateRegistrationPDF'])->name('modules.hr.employees.registration-pdf');
    Route::post('/modules/hr/employees/bulk-action', [EmployeeController::class, 'bulkAction'])->name('modules.hr.employees.bulk-action')->middleware('role:HR Officer,System Admin');
    Route::post('/modules/hr/employees/bulk-sms', [EmployeeController::class, 'bulkSMS'])->name('modules.hr.employees.bulk-sms')->middleware('role:HR Officer,System Admin');
    Route::post('/modules/hr/employees/bulk-generate-passwords-sms', [EmployeeController::class, 'bulkGeneratePasswordsAndSendSMS'])->name('modules.hr.employees.bulk-generate-passwords-sms')->middleware('role:HR Officer,System Admin');
    Route::get('/modules/hr/employees/report', [EmployeeController::class, 'generateReport'])->name('modules.hr.employees.report')->middleware('role:HR Officer,System Admin');
    Route::get('/modules/hr/payroll', [PayrollController::class, 'index'])->name('modules.hr.payroll');
    Route::get('/modules/hr/sick-sheets', [App\Http\Controllers\SickSheetController::class, 'index'])->name('modules.hr.sick-sheets');
    Route::get('/modules/hr/assessments', [App\Http\Controllers\AssessmentController::class, 'index'])->name('modules.hr.assessments');
    Route::get('/modules/hr/departments', [App\Http\Controllers\DepartmentController::class, 'index'])->name('modules.hr.departments');
    Route::get('/modules/hr/positions', [App\Http\Controllers\PositionController::class, 'index'])->name('modules.hr.positions');
    Route::get('/modules/hr/recruitment', [App\Http\Controllers\RecruitmentController::class, 'index'])->name('modules.hr.recruitment');
    Route::post('/modules/hr/recruitment/handle', [App\Http\Controllers\RecruitmentController::class, 'handleRequest'])->name('recruitment.handle');
    Route::get('/modules/hr/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('modules.hr.attendance');
    Route::get('/modules/hr/attendance/settings', [App\Http\Controllers\AttendanceSettingsController::class, 'index'])->name('modules.hr.attendance.settings')->middleware('role:HR Officer,System Admin');
    Route::get('/modules/hr/attendance/settings/devices', [App\Http\Controllers\AttendanceSettingsController::class, 'devices'])->name('modules.hr.attendance.settings.devices')->middleware('role:HR Officer,System Admin');
    Route::get('/modules/hr/attendance/settings/enrollment', [App\Http\Controllers\AttendanceSettingsController::class, 'enrollment'])->name('modules.hr.attendance.settings.enrollment')->middleware('role:HR Officer,System Admin');
    Route::get('/modules/hr/attendance/settings/schedules', [App\Http\Controllers\AttendanceSettingsController::class, 'schedules'])->name('modules.hr.attendance.settings.schedules')->middleware('role:HR Officer,System Admin');
    Route::get('/modules/hr/attendance/settings/policies', [App\Http\Controllers\AttendanceSettingsController::class, 'policies'])->name('modules.hr.attendance.settings.policies')->middleware('role:HR Officer,System Admin');
    Route::post('/modules/hr/attendance/settings/general', [App\Http\Controllers\AttendanceSettingsController::class, 'saveGeneralSettings'])->name('modules.hr.attendance.settings.general')->middleware('role:HR Officer,System Admin');
    
    // ZKTeco Individual Pages
    Route::get('/modules/hr/zkteco/test-connection', [App\Http\Controllers\ZKTecoTestController::class, 'testConnection'])->name('zkteco.test')->middleware('role:HR Officer,System Admin');
    Route::get('/modules/hr/zkteco/register-user', [App\Http\Controllers\ZKTecoTestController::class, 'registerUser'])->name('zkteco.register')->middleware('role:HR Officer,System Admin');
    Route::get('/modules/hr/zkteco/retrieve-data', [App\Http\Controllers\ZKTecoTestController::class, 'retrieveData'])->name('zkteco.retrieve')->middleware('role:HR Officer,System Admin');
    
    // ZKTeco API Endpoints
    Route::post('/api/zkteco/test-connection', [App\Http\Controllers\ZKTecoTestController::class, 'apiTestConnection'])->name('zkteco.test.api')->middleware('role:HR Officer,System Admin');
    Route::post('/api/zkteco/register-user', [App\Http\Controllers\ZKTecoTestController::class, 'apiRegisterUser'])->name('zkteco.register.api')->middleware('role:HR Officer,System Admin');
    Route::post('/api/zkteco/retrieve-data', [App\Http\Controllers\ZKTecoTestController::class, 'apiRetrieveData'])->name('zkteco.retrieve.api')->middleware('role:HR Officer,System Admin');
});




// Leave Management Routes
Route::prefix('leave')->name('leave.')->group(function () {
    // Basic CRUD
    Route::post('/', [LeaveController::class, 'store'])->name('store');
    Route::get('/{leaveRequest}', [LeaveController::class, 'show'])->name('show');
    Route::put('/{leaveRequest}', [LeaveController::class, 'update'])->name('update');
    Route::delete('/{leaveRequest}', [LeaveController::class, 'cancelRequest'])->name('cancel');
    
    // AJAX endpoints
    Route::post('/annual-balance', [LeaveController::class, 'getAnnualBalance'])->name('annual-balance');
    Route::post('/recommendations', [LeaveController::class, 'getLeaveRecommendations'])->name('recommendations');
    Route::post('/check-active', [LeaveController::class, 'checkActiveLeave'])->name('check-active');
    Route::get('/{leaveRequest}/edit', [LeaveController::class, 'getRequestForEdit'])->name('edit');
    
    // Review workflow
    Route::post('/{leaveRequest}/hr-review', [LeaveController::class, 'hrReview'])->name('hr-review');
    Route::post('/{leaveRequest}/hod-review', [LeaveController::class, 'hodReview'])->name('hod-review');
    Route::post('/{leaveRequest}/ceo-review', [LeaveController::class, 'ceoReview'])->name('ceo-review');
    Route::post('/{leaveRequest}/process-documents', [LeaveController::class, 'processDocuments'])->name('process-documents');
    Route::post('/{leaveRequest}/return-form', [LeaveController::class, 'submitReturnForm'])->name('return-form');
    
    // HR Management
    Route::post('/hr/all-requests', [LeaveController::class, 'getAllRequests'])->name('hr.all-requests');
    Route::post('/hr/analytics', [LeaveController::class, 'getAnalytics'])->name('hr.analytics');
    Route::post('/hr/manage-balance', [LeaveController::class, 'manageLeaveBalance'])->name('hr.manage-balance');
    Route::post('/hr/balance-data', [LeaveController::class, 'getBalanceData'])->name('hr.balance-data');
    Route::post('/hr/manage-recommendations', [LeaveController::class, 'manageRecommendations'])->name('hr.manage-recommendations');
    Route::post('/hr/bulk-operations', [LeaveController::class, 'bulkOperations'])->name('hr.bulk-operations');
    Route::get('/hr/bulk-operations', [LeaveController::class, 'bulkOperationsExport'])->name('hr.bulk-operations');
    
    // Leave Type Management (HR only)
    Route::get('/hr/leave-types', [LeaveController::class, 'leaveTypesIndex'])->name('hr.leave-types')->middleware('role:HR Officer,System Admin');
    Route::get('/hr/leave-types/{leaveType}', [LeaveController::class, 'getLeaveType'])->name('hr.leave-types.show')->middleware('role:HR Officer,System Admin');
    Route::post('/hr/leave-types', [LeaveController::class, 'storeLeaveType'])->name('hr.leave-types.store')->middleware('role:HR Officer,System Admin');
    Route::put('/hr/leave-types/{leaveType}', [LeaveController::class, 'updateLeaveType'])->name('hr.leave-types.update')->middleware('role:HR Officer,System Admin');
    Route::delete('/hr/leave-types/{leaveType}', [LeaveController::class, 'destroyLeaveType'])->name('hr.leave-types.destroy')->middleware('role:HR Officer,System Admin');
    
    // PDF Generation
    Route::get('/{leaveRequest}/pdf/certificate', [LeaveController::class, 'generateLeaveCertificatePdf'])->name('pdf.certificate');
    Route::get('/{leaveRequest}/pdf/fare-certificate', [LeaveController::class, 'generateFareCertificatePdf'])->name('pdf.fare-certificate');
    Route::get('/{leaveRequest}/pdf/approval-letter', [LeaveController::class, 'generateApprovalLetterPdf'])->name('pdf.approval-letter');
    Route::get('/{leaveRequest}/pdf/summary', [LeaveController::class, 'generateLeaveSummaryPdf'])->name('pdf.summary');
    
    // Document Preview (HTML)
    Route::get('/{leaveRequest}/preview/certificate', [LeaveController::class, 'previewLeaveCertificate'])->name('preview.certificate');
    Route::get('/{leaveRequest}/preview/fare-certificate', [LeaveController::class, 'previewFareCertificate'])->name('preview.fare-certificate');
    Route::get('/{leaveRequest}/preview/combined-certificate', [LeaveController::class, 'previewCombinedCertificate'])->name('preview.combined-certificate');
});

// Permission Management Routes
// Permission Request Routes (Staff Permission to be Outside Office)
Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::get('/calendar/events', [PermissionController::class, 'getCalendarEvents'])->name('calendar.events');
    Route::get('/analytics', [PermissionController::class, 'getAnalytics'])->name('analytics');
    Route::post('/', [PermissionController::class, 'store'])->name('store');
    Route::get('/{id}', [PermissionController::class, 'show'])->name('show');
    Route::get('/{id}/pdf', [PermissionController::class, 'generatePdf'])->name('pdf');
    Route::post('/{id}/hr-initial-review', [PermissionController::class, 'hrInitialReview'])->name('hr-initial-review')->middleware('role:HR Officer,System Admin');
    Route::post('/{id}/hod-review', [PermissionController::class, 'hodReview'])->name('hod-review')->middleware('role:HOD,System Admin');
    Route::post('/{id}/hr-final-approval', [PermissionController::class, 'hrFinalApproval'])->name('hr-final-approval')->middleware('role:HR Officer,System Admin');
    Route::post('/{id}/confirm-return', [PermissionController::class, 'confirmReturn'])->name('confirm-return');
    Route::post('/{id}/hr-return-verification', [PermissionController::class, 'hrReturnVerification'])->name('hr-return-verification')->middleware('role:HR Officer,System Admin');
});

// Sick Sheet Routes
Route::prefix('sick-sheets')->name('sick-sheets.')->group(function () {
    Route::post('/', [App\Http\Controllers\SickSheetController::class, 'store'])->name('store');
    Route::post('/{sickSheet}/hr-review', [App\Http\Controllers\SickSheetController::class, 'hrReview'])->name('hr-review')->middleware('role:HR Officer,System Admin');
    Route::post('/{sickSheet}/hod-approve', [App\Http\Controllers\SickSheetController::class, 'hodApprove'])->name('hod-approve')->middleware('role:HOD,System Admin');
    Route::post('/{sickSheet}/confirm-return', [App\Http\Controllers\SickSheetController::class, 'confirmReturn'])->name('confirm-return');
    Route::post('/{sickSheet}/hr-verification', [App\Http\Controllers\SickSheetController::class, 'hrFinalVerification'])->name('hr-verification')->middleware('role:HR Officer,System Admin');
});

// Assessment Routes
Route::prefix('assessments')->name('assessments.')->group(function () {
    // Page routes (GET)
    Route::get('/create', [App\Http\Controllers\AssessmentController::class, 'create'])->name('create');
    Route::get('/{assessment}/edit', [App\Http\Controllers\AssessmentController::class, 'edit'])->name('edit')->middleware('role:System Admin,HR Officer');
    Route::get('/{assessment}/approve', [App\Http\Controllers\AssessmentController::class, 'approvePage'])->name('approve')->middleware('role:HOD,System Admin,HR Officer');
    Route::get('/{assessment}/reject', [App\Http\Controllers\AssessmentController::class, 'rejectPage'])->name('reject')->middleware('role:HOD,System Admin,HR Officer');
    Route::get('/activities/{activity}/reports', [App\Http\Controllers\AssessmentController::class, 'activityReportsPage'])->name('activities.reports');
    Route::get('/activities/{activity}/progress/create', [App\Http\Controllers\AssessmentController::class, 'progressCreatePage'])->name('progress.create');
    Route::get('/analytics', [App\Http\Controllers\AssessmentController::class, 'analyticsPage'])->name('analytics.page')->middleware('role:System Admin,HR Officer,HOD');
    Route::get('/analytics/data', [App\Http\Controllers\AssessmentController::class, 'getAnalytics'])->name('analytics.data')->middleware('role:System Admin,HR Officer,HOD');
    
    // API/Action routes
    Route::post('/', [App\Http\Controllers\AssessmentController::class, 'store'])->name('store');
    Route::post('/{assessment}/hod-approve', [App\Http\Controllers\AssessmentController::class, 'hodApprove'])->name('hod-approve')->middleware('role:HOD,System Admin,HR Officer');
    Route::post('/activities/{activity}/progress-report', [App\Http\Controllers\AssessmentController::class, 'submitProgressReport'])->name('progress-report');
    Route::post('/progress-reports/{report}/approve', [App\Http\Controllers\AssessmentController::class, 'approveProgressReport'])->name('progress-approve')->middleware('role:HOD,System Admin');
    Route::get('/{assessment}/details', [App\Http\Controllers\AssessmentController::class, 'getAssessmentDetails'])->name('details');
    Route::get('/performance/{employeeId?}', [App\Http\Controllers\AssessmentController::class, 'calculatePerformance'])->name('performance');
    Route::get('/export/{employeeId}', [App\Http\Controllers\AssessmentController::class, 'exportPerformance'])->name('export');
    Route::get('/comprehensive-data', [App\Http\Controllers\AssessmentController::class, 'getComprehensiveData'])->name('comprehensive-data')->middleware('role:System Admin,HR Officer');
    Route::get('/calendar/events', [App\Http\Controllers\AssessmentController::class, 'getCalendarEvents'])->name('calendar.events');
    Route::get('/{assessment}', [App\Http\Controllers\AssessmentController::class, 'show'])->name('show');
    
    // Admin Management Routes
    Route::put('/{assessment}', [App\Http\Controllers\AssessmentController::class, 'update'])->name('update')->middleware('role:System Admin,HR Officer');
    Route::delete('/{assessment}', [App\Http\Controllers\AssessmentController::class, 'destroy'])->name('destroy')->middleware('role:System Admin');
    Route::put('/activities/{activity}', [App\Http\Controllers\AssessmentController::class, 'updateActivity'])->name('activities.update')->middleware('role:System Admin,HR Officer');
    Route::delete('/activities/{activity}', [App\Http\Controllers\AssessmentController::class, 'destroyActivity'])->name('activities.destroy')->middleware('role:System Admin');
    Route::delete('/progress-reports/{report}', [App\Http\Controllers\AssessmentController::class, 'destroyProgressReport'])->name('progress-reports.destroy')->middleware('role:System Admin');
});

// Department Management Routes (HR/Admin only)
Route::prefix('departments')->name('departments.')->middleware('role:HR Officer,System Admin')->group(function () {
    Route::get('/', [App\Http\Controllers\DepartmentController::class, 'index'])->name('index');
    Route::get('/{department}', [App\Http\Controllers\DepartmentController::class, 'show'])->name('show');
    Route::post('/', [App\Http\Controllers\DepartmentController::class, 'store'])->name('store');
    Route::put('/{department}', [App\Http\Controllers\DepartmentController::class, 'update'])->name('update');
    Route::delete('/{department}', [App\Http\Controllers\DepartmentController::class, 'destroy'])->name('destroy');
});

// Position Management Routes (HR/Admin only)
Route::prefix('positions')->name('positions.')->middleware('role:HR Officer,System Admin')->group(function () {
    Route::get('/', [App\Http\Controllers\PositionController::class, 'index'])->name('index');
    Route::get('/{position}', [App\Http\Controllers\PositionController::class, 'show'])->name('show');
    Route::post('/', [App\Http\Controllers\PositionController::class, 'store'])->name('store');
    Route::put('/{position}', [App\Http\Controllers\PositionController::class, 'update'])->name('update');
    Route::delete('/{position}', [App\Http\Controllers\PositionController::class, 'destroy'])->name('destroy');
});

// Employee Management Routes
Route::prefix('employees')->name('employees.')->group(function () {
    Route::post('/', [EmployeeController::class, 'store'])->name('store')->middleware('role:HR Officer,System Admin');
    Route::post('/sync-all', [EmployeeController::class, 'syncAllEmployees'])->name('sync-all')->middleware('role:HR Officer,System Admin');
    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit')->middleware('role:HR Officer,System Admin');
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update')->middleware('role:HR Officer,System Admin');
    Route::post('/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('toggle-status')->middleware('role:HR Officer,System Admin');
    Route::post('/{employee}/upload-photo', [EmployeeController::class, 'uploadPhoto'])->name('upload-photo');
    Route::get('/{employee}/registration-pdf', [EmployeeController::class, 'generateRegistrationPDF'])->name('registration-pdf')->middleware('role:HR Officer,System Admin');
    Route::post('/{employee}/send-sms', [EmployeeController::class, 'sendSMS'])->name('send-sms')->middleware('role:HR Officer,System Admin');
    
    // Document Management Routes
    Route::post('/{employee}/documents', [EmployeeController::class, 'uploadDocuments'])->name('documents.upload')->middleware('role:HR Officer,System Admin');
    Route::get('/{employee}/documents/{document}', [EmployeeController::class, 'getDocument'])->name('documents.show');
    Route::get('/{employee}/documents/{document}/download', [EmployeeController::class, 'downloadDocument'])->name('documents.download');
    Route::delete('/{employee}/documents/{document}', [EmployeeController::class, 'deleteDocument'])->name('documents.delete')->middleware('role:HR Officer,System Admin');
});

// Attendance Management Routes
Route::prefix('attendance')->name('attendance.')->group(function () {
    // Public routes (for employees to clock in/out)
    Route::post('/time-in', [App\Http\Controllers\AttendanceController::class, 'timeIn'])->name('time-in');
    Route::post('/time-out', [App\Http\Controllers\AttendanceController::class, 'timeOut'])->name('time-out');
    Route::post('/break-start', [App\Http\Controllers\AttendanceController::class, 'breakStart'])->name('break-start');
    Route::post('/break-end', [App\Http\Controllers\AttendanceController::class, 'breakEnd'])->name('break-end');
    Route::get('/current-status', [App\Http\Controllers\AttendanceController::class, 'getCurrentStatus'])->name('current-status');
    Route::get('/today', [App\Http\Controllers\AttendanceController::class, 'today'])->name('today');
    Route::post('/validate-location', [App\Http\Controllers\AttendanceController::class, 'validateLocation'])->name('validate-location');
    Route::get('/office-location', [App\Http\Controllers\AttendanceController::class, 'getOfficeLocation'])->name('office-location');
    Route::get('/export', [App\Http\Controllers\AttendanceController::class, 'export'])->name('export');
    
    // HR Management routes (must be after specific routes to avoid route conflicts)
    // IMPORTANT: Specific routes (like /delete-all) must come BEFORE parameterized routes (like /{id})
    Route::post('/', [App\Http\Controllers\AttendanceController::class, 'store'])->name('store')->middleware('role:HR Officer,System Admin');
    Route::delete('/delete-all', [App\Http\Controllers\AttendanceController::class, 'deleteAll'])->name('delete-all')->middleware('role:HR Officer,System Admin');
    Route::post('/{id}/verify', [App\Http\Controllers\AttendanceController::class, 'verify'])->name('verify')->middleware('role:HR Officer,System Admin');
    Route::put('/{id}', [App\Http\Controllers\AttendanceController::class, 'update'])->name('update')->middleware('role:HR Officer,System Admin');
    Route::delete('/{id}', [App\Http\Controllers\AttendanceController::class, 'destroy'])->name('destroy')->middleware('role:HR Officer,System Admin');
    Route::get('/{id}/download-pdf', [App\Http\Controllers\AttendanceController::class, 'downloadPDF'])->name('download-pdf');
    Route::get('/{id}', [App\Http\Controllers\AttendanceController::class, 'show'])->name('show');
    
    // API routes for biometric devices and mobile apps
    Route::post('/api/biometric', [App\Http\Controllers\AttendanceController::class, 'biometricRecord'])->name('api.biometric');
});

// Device API Routes (Public - for device communication)
Route::prefix('api/device')->name('device.api.')->group(function () {
    // Health check
    Route::get('/health', [App\Http\Controllers\DeviceApiController::class, 'healthCheck'])->name('health');
    
    // Receiving data FROM device (Push API)
    Route::post('/attendance/push', [App\Http\Controllers\DeviceApiController::class, 'receiveAttendance'])->name('attendance.push');
    Route::post('/attendance/batch', [App\Http\Controllers\DeviceApiController::class, 'receiveBatchAttendance'])->name('attendance.batch');
    Route::post('/status', [App\Http\Controllers\DeviceApiController::class, 'receiveDeviceStatus'])->name('status');
    
    // Sending data TO device (Pull API)
    Route::get('/users/{device_id}', [App\Http\Controllers\DeviceApiController::class, 'getUsersForDevice'])->name('users.list');
    Route::get('/users/{device_id}/{employee_id}', [App\Http\Controllers\DeviceApiController::class, 'getUserForDevice'])->name('users.get');
    Route::get('/time/{device_id}', [App\Http\Controllers\DeviceApiController::class, 'getServerTime'])->name('time');
    Route::get('/commands/{device_id}', [App\Http\Controllers\DeviceApiController::class, 'getDeviceCommands'])->name('commands.get');
    Route::post('/commands/{device_id}', [App\Http\Controllers\DeviceApiController::class, 'sendDeviceCommand'])->name('commands.send');
});

// Attendance Settings Routes (Admin/HR only)
Route::prefix('attendance-settings')->name('attendance-settings.')->middleware('role:HR Officer,System Admin')->group(function () {
    // API endpoints
    Route::post('/employees/list', [App\Http\Controllers\AttendanceSettingsController::class, 'getEmployeesList'])->name('get-employees-list');
    
    // Locations
    Route::get('/locations', [App\Http\Controllers\AttendanceSettingsController::class, 'getLocations'])->name('locations.index');
    Route::post('/locations', [App\Http\Controllers\AttendanceSettingsController::class, 'storeLocation'])->name('locations.store');
    Route::put('/locations/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'updateLocation'])->name('locations.update');
    Route::delete('/locations/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'deleteLocation'])->name('locations.delete');
    
    // Devices
    Route::get('/devices', [App\Http\Controllers\AttendanceSettingsController::class, 'getDevices'])->name('devices.index');
    Route::get('/devices/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'getDevice'])->name('devices.show');
    Route::post('/devices', [App\Http\Controllers\AttendanceSettingsController::class, 'storeDevice'])->name('devices.store');
    Route::put('/devices/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'updateDevice'])->name('devices.update');
    Route::delete('/devices/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'deleteDevice'])->name('devices.delete');
    Route::post('/devices/{id}/test', [App\Http\Controllers\AttendanceSettingsController::class, 'testDevice'])->name('devices.test');
    Route::get('/devices/{id}/logs', [App\Http\Controllers\AttendanceSettingsController::class, 'getDeviceLogs'])->name('devices.logs');
    
    // Work Schedules
    Route::post('/schedules', [App\Http\Controllers\AttendanceSettingsController::class, 'storeSchedule'])->name('schedules.store');
    Route::put('/schedules/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/schedules/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'deleteSchedule'])->name('schedules.delete');
    
    // Policies
    Route::post('/policies', [App\Http\Controllers\AttendanceSettingsController::class, 'storePolicy'])->name('policies.store');
    Route::put('/policies/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'updatePolicy'])->name('policies.update');
    Route::delete('/policies/{id}', [App\Http\Controllers\AttendanceSettingsController::class, 'deletePolicy'])->name('policies.delete');
    Route::post('/policies/save-step', [App\Http\Controllers\AttendanceSettingsController::class, 'savePolicyStep'])->name('policies.save-step');
    Route::post('/policies/{id}/save-step', [App\Http\Controllers\AttendanceSettingsController::class, 'savePolicyStep'])->name('policies.save-step-update');
    
    // Devices - Step by step save
    Route::post('/devices/save-step', [App\Http\Controllers\AttendanceSettingsController::class, 'saveDeviceStep'])->name('devices.save-step');
    Route::post('/devices/{id}/save-step', [App\Http\Controllers\AttendanceSettingsController::class, 'saveDeviceStep'])->name('devices.save-step-update');
    
    // Employees API
    Route::get('/api/employees', [App\Http\Controllers\AttendanceSettingsController::class, 'getEmployeesList'])->name('api.employees');
    Route::get('/devices', [App\Http\Controllers\AttendanceSettingsController::class, 'getDevices'])->name('devices.index');
    Route::post('/users/enroll', [App\Http\Controllers\AttendanceSettingsController::class, 'enrollUser'])->name('users.enroll');
    Route::post('/users/sync-all', [App\Http\Controllers\AttendanceSettingsController::class, 'syncAllUsers'])->name('users.sync-all');
    
    // Device Sync - Capture attendance from devices
    Route::post('/devices/sync-all', [App\Http\Controllers\AttendanceSettingsController::class, 'syncAllDevices'])->name('devices.sync-all');
    Route::post('/devices/{id}/sync', [App\Http\Controllers\AttendanceSettingsController::class, 'syncDevice'])->name('devices.sync');
    
    // Dashboard Data
    Route::get('/dashboard/data', [App\Http\Controllers\AttendanceSettingsController::class, 'getDashboardData'])->name('dashboard.data');
    
    // Reports
    Route::post('/reports/generate', [App\Http\Controllers\AttendanceSettingsController::class, 'generateReport'])->name('reports.generate');
    Route::get('/reports/export', [App\Http\Controllers\AttendanceSettingsController::class, 'exportReport'])->name('reports.export');
    
    // Notifications
    Route::post('/notifications/save', [App\Http\Controllers\AttendanceSettingsController::class, 'saveNotificationSettings'])->name('notifications.save');
    Route::post('/notifications/test-sms', [App\Http\Controllers\AttendanceSettingsController::class, 'testSMS'])->name('notifications.test-sms');
    
    // Advanced Settings
    Route::post('/advanced/save-sync', [App\Http\Controllers\AttendanceSettingsController::class, 'saveSyncSettings'])->name('advanced.save-sync');
    Route::post('/advanced/save-failure', [App\Http\Controllers\AttendanceSettingsController::class, 'saveFailureSettings'])->name('advanced.save-failure');
    Route::post('/advanced/save-security', [App\Http\Controllers\AttendanceSettingsController::class, 'saveSecuritySettings'])->name('advanced.save-security');
    Route::post('/advanced/run-maintenance', [App\Http\Controllers\AttendanceSettingsController::class, 'runMaintenance'])->name('advanced.run-maintenance');
    Route::post('/advanced/clear-cache', [App\Http\Controllers\AttendanceSettingsController::class, 'clearCache'])->name('advanced.clear-cache');
    Route::get('/advanced/system-health', [App\Http\Controllers\AttendanceSettingsController::class, 'checkSystemHealth'])->name('advanced.system-health');
    Route::post('/devices/sync-all', [App\Http\Controllers\AttendanceSettingsController::class, 'syncAllDevices'])->name('devices.sync-all');
    Route::get('/devices/failures', [App\Http\Controllers\AttendanceSettingsController::class, 'checkDeviceFailures'])->name('devices.failures');
    Route::post('/devices/test-all', [App\Http\Controllers\AttendanceSettingsController::class, 'testAllDevices'])->name('devices.test-all');
    Route::post('/devices/{id}/retry', [App\Http\Controllers\AttendanceSettingsController::class, 'retryDevice'])->name('devices.retry');
});

// ZKTeco Device Routes
Route::prefix('zkteco')->name('zkteco.')->middleware('auth')->group(function () {
    Route::post('/test-connection', [App\Http\Controllers\ZKTecoController::class, 'testConnection'])->name('test-connection');
    Route::post('/device-info', [App\Http\Controllers\ZKTecoController::class, 'getDeviceInfo'])->name('device-info');
    Route::post('/users/{id}/register', [App\Http\Controllers\ZKTecoController::class, 'registerUser'])->name('users.register');
    Route::post('/users/{id}/register-to-device', [App\Http\Controllers\ZKTecoController::class, 'registerUserToDevice'])->name('users.register-to-device');
    Route::post('/users/capture-from-device', [App\Http\Controllers\ZKTecoController::class, 'captureUsersFromDevice'])->name('users.capture-from-device');
    Route::post('/users/{id}/unregister', [App\Http\Controllers\ZKTecoController::class, 'unregisterUser'])->name('users.unregister');
    Route::post('/users/sync-from-device', [App\Http\Controllers\ZKTecoController::class, 'syncUsersFromDevice'])->name('users.sync-from-device');
    Route::post('/users/sync-to-device', [App\Http\Controllers\ZKTecoController::class, 'syncUsersToDevice'])->name('users.sync-to-device');
    Route::post('/attendance/sync', [App\Http\Controllers\ZKTecoController::class, 'syncAttendance'])->name('attendance.sync');
    Route::post('/attendance/sync-from-api', [App\Http\Controllers\ZKTecoController::class, 'syncAttendanceFromApi'])->name('attendance.sync-from-api');
    Route::get('/attendance/device', [App\Http\Controllers\ZKTecoController::class, 'getDeviceAttendance'])->name('attendance.device');
    Route::get('/attendance/device/test', [App\Http\Controllers\ZKTecoController::class, 'testDeviceApiConnection'])->name('attendance.device.test');
    Route::post('/users/{id}/check-fingerprints', [App\Http\Controllers\ZKTecoController::class, 'checkFingerprints'])->name('users.check-fingerprints');
});

// ZKTeco Push SDK Routes (No auth required - called by device)
Route::prefix('iclock')->group(function () {
    Route::get('/getrequest', [App\Http\Controllers\PushSDKController::class, 'getRequest'])->name('push.getrequest');
    Route::post('/cdata', [App\Http\Controllers\PushSDKController::class, 'cdata'])->name('push.cdata');
});

// API Routes
Route::prefix('api/v1')->name('api.v1.')->group(function () {
    // Attendance API
    Route::get('/attendances', [App\Http\Controllers\Api\AttendanceApiController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/{id}', [App\Http\Controllers\Api\AttendanceApiController::class, 'show'])->name('attendances.show');
    Route::get('/attendances/daily/{date}', [App\Http\Controllers\Api\AttendanceApiController::class, 'daily'])->name('attendances.daily');
    
    // User API
    Route::get('/users', [App\Http\Controllers\Api\UserApiController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [App\Http\Controllers\Api\UserApiController::class, 'show'])->name('users.show');
    Route::post('/users', [App\Http\Controllers\Api\UserApiController::class, 'store'])->name('users.store');
    Route::post('/users/register', [App\Http\Controllers\Api\UserApiController::class, 'register'])->name('users.register');
    Route::post('/users/{id}/generate-enroll-id', [App\Http\Controllers\Api\UserApiController::class, 'generateEnrollId'])->name('users.generate-enroll-id');
});
    
    // Payroll Management Routes
    Route::prefix('payroll')->name('payroll.')->group(function () {
        // Individual Page Routes (GET)
        Route::get('/process', [PayrollController::class, 'showProcessPage'])->name('process.page');
        Route::get('/{payroll}/review', [PayrollController::class, 'showReviewPage'])->name('review.page');
        Route::get('/{payroll}/approve', [PayrollController::class, 'showApprovePage'])->name('approve.page');
        Route::get('/{payroll}/pay', [PayrollController::class, 'showPayPage'])->name('pay.page');
        Route::get('/{payroll}/view', [PayrollController::class, 'showViewPage'])->name('view.page');
        Route::get('/payslip/{payrollItem}/view', [PayrollController::class, 'showPayslipPage'])->name('payslip.page');
        
        // Action Routes (POST)
        Route::post('/process', [PayrollController::class, 'processPayroll'])->name('process');
        Route::post('/{payroll}/review', [PayrollController::class, 'reviewPayroll'])->name('review');
        Route::post('/{payroll}/approve', [PayrollController::class, 'approvePayroll'])->name('approve');
        Route::post('/{payroll}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('mark-paid');
        Route::get('/{payroll}/details', [PayrollController::class, 'getPayrollDetails'])->name('details');
        Route::get('/payslip/{payrollItem}', [PayrollController::class, 'getPayslip'])->name('payslip');
        Route::post('/calculate-deductions', [PayrollController::class, 'calculateEmployeeDeductions'])->name('calculate-deductions');
        Route::get('/{payroll}/export', [PayrollController::class, 'exportPayroll'])->name('export');
        // PDF Generation Routes
        Route::get('/payslip/{payrollItem}/pdf', [PayrollController::class, 'generatePayslipPdf'])->name('payslip.pdf');
        Route::get('/{payroll}/report/pdf', [PayrollController::class, 'generatePayrollReportPdf'])->name('report.pdf');
        
        // Deduction Management Routes
        Route::get('/deductions', [PayrollController::class, 'showDeductionManagement'])->name('deductions.index');
        Route::get('/deductions/summary', [PayrollController::class, 'getDeductionsSummary'])->name('deductions.summary');
        Route::get('/deductions/employee/{employeeId}', [PayrollController::class, 'getEmployeeDeductions'])->name('deductions.employee');
        Route::post('/deductions', [PayrollController::class, 'storeDeduction'])->name('deductions.store');
        Route::put('/deductions/{deductionId}', [PayrollController::class, 'updateDeduction'])->name('deductions.update');
        Route::delete('/deductions/{deductionId}', [PayrollController::class, 'deleteDeduction'])->name('deductions.delete');
        Route::post('/deductions/bulk', [PayrollController::class, 'createBulkDeductions'])->name('deductions.bulk');
        Route::post('/deductions/bulk/preview', [PayrollController::class, 'calculateBulkDeductionPreview'])->name('deductions.bulk.preview');
        
        // Overtime Management Routes
        Route::get('/overtime', [App\Http\Controllers\PayrollOvertimeController::class, 'index'])->name('overtime.index');
        Route::post('/overtime', [App\Http\Controllers\PayrollOvertimeController::class, 'store'])->name('overtime.store');
        Route::put('/overtime/{overtime}', [App\Http\Controllers\PayrollOvertimeController::class, 'update'])->name('overtime.update');
        Route::delete('/overtime/{overtime}', [App\Http\Controllers\PayrollOvertimeController::class, 'destroy'])->name('overtime.destroy');
        Route::post('/overtime/bulk', [App\Http\Controllers\PayrollOvertimeController::class, 'bulkStore'])->name('overtime.bulk');
        Route::get('/overtime/by-month', [App\Http\Controllers\PayrollOvertimeController::class, 'getByMonth'])->name('overtime.by-month');
        Route::get('/overtime/template', [App\Http\Controllers\PayrollOvertimeController::class, 'downloadTemplate'])->name('overtime.template');
        
        // Bonus Management Routes
        Route::get('/bonus', [App\Http\Controllers\PayrollBonusController::class, 'index'])->name('bonus.index');
        Route::post('/bonus', [App\Http\Controllers\PayrollBonusController::class, 'store'])->name('bonus.store');
        Route::put('/bonus/{bonus}', [App\Http\Controllers\PayrollBonusController::class, 'update'])->name('bonus.update');
        Route::delete('/bonus/{bonus}', [App\Http\Controllers\PayrollBonusController::class, 'destroy'])->name('bonus.destroy');
        Route::post('/bonus/bulk', [App\Http\Controllers\PayrollBonusController::class, 'bulkStore'])->name('bonus.bulk');
        Route::get('/bonus/by-month', [App\Http\Controllers\PayrollBonusController::class, 'getByMonth'])->name('bonus.by-month');
        Route::get('/bonus/template', [App\Http\Controllers\PayrollBonusController::class, 'downloadTemplate'])->name('bonus.template');
        
        // Allowance Management Routes
        Route::get('/allowance', [App\Http\Controllers\PayrollAllowanceController::class, 'index'])->name('allowance.index');
        Route::post('/allowance', [App\Http\Controllers\PayrollAllowanceController::class, 'store'])->name('allowance.store');
        Route::put('/allowance/{allowance}', [App\Http\Controllers\PayrollAllowanceController::class, 'update'])->name('allowance.update');
        Route::delete('/allowance/{allowance}', [App\Http\Controllers\PayrollAllowanceController::class, 'destroy'])->name('allowance.destroy');
        Route::post('/allowance/bulk', [App\Http\Controllers\PayrollAllowanceController::class, 'bulkStore'])->name('allowance.bulk');
        Route::get('/allowance/by-month', [App\Http\Controllers\PayrollAllowanceController::class, 'getByMonth'])->name('allowance.by-month');
        Route::get('/allowance/template', [App\Http\Controllers\PayrollAllowanceController::class, 'downloadTemplate'])->name('allowance.template');
    });
    
    // Incident Management Routes
    Route::prefix('modules/incidents')->group(function(){
        // Specific routes must come before dynamic routes
        Route::get('/', [App\Http\Controllers\IncidentController::class, 'index'])->name('modules.incidents');
        Route::get('/dashboard', [App\Http\Controllers\IncidentController::class, 'dashboard'])->name('modules.incidents.dashboard');
        Route::get('/create', [App\Http\Controllers\IncidentController::class, 'create'])->name('modules.incidents.create');
        Route::post('/', [App\Http\Controllers\IncidentController::class, 'store'])->name('modules.incidents.store');
        Route::get('/analytics', [App\Http\Controllers\IncidentController::class, 'analyticsPage'])->name('modules.incidents.analytics');
        Route::get('/analytics/data', [App\Http\Controllers\IncidentController::class, 'analytics'])->name('modules.incidents.analytics.data');
        Route::get('/export', [App\Http\Controllers\IncidentController::class, 'exportPage'])->name('modules.incidents.export');
        Route::post('/export/download', [App\Http\Controllers\IncidentController::class, 'export'])->name('modules.incidents.export.download');
        Route::post('/bulk-action', [App\Http\Controllers\IncidentController::class, 'bulkAction'])->name('modules.incidents.bulk.action');
        Route::post('/action', [App\Http\Controllers\IncidentsController::class, 'action'])->name('modules.incidents.action');
        Route::post('/sync-emails', [App\Http\Controllers\IncidentController::class, 'syncEmails'])->name('modules.incidents.sync.emails');
        Route::get('/sync-status', [App\Http\Controllers\IncidentController::class, 'getSyncStatus'])->name('modules.incidents.sync.status');
        Route::get('/email-configs', [App\Http\Controllers\IncidentController::class, 'getEmailConfigs'])->name('modules.incidents.email.configs');
        Route::get('/email-configs/connection-statuses', [App\Http\Controllers\IncidentController::class, 'getAllConnectionStatuses'])->name('modules.incidents.email.configs.statuses');
        
        // Email configuration routes (must come before /{id} route)
        Route::get('/email-config', [App\Http\Controllers\IncidentController::class, 'emailConfig'])->name('modules.incidents.email.config');
        Route::get('/email-accounts', [App\Http\Controllers\IncidentController::class, 'emailAccounts'])->name('modules.incidents.email.accounts');
        Route::get('/email-connection-test', [App\Http\Controllers\IncidentController::class, 'emailConnectionTest'])->name('modules.incidents.email.connection.test');
        Route::get('/email-retrieve', [App\Http\Controllers\IncidentController::class, 'emailRetrieve'])->name('modules.incidents.email.retrieve');
        Route::get('/email-transfer', [App\Http\Controllers\IncidentController::class, 'emailTransfer'])->name('modules.incidents.email.transfer');
        Route::post('/email-config', [App\Http\Controllers\IncidentController::class, 'storeEmailConfig'])->name('modules.incidents.email.config.store');
        Route::post('/email-config/test-without-save', [App\Http\Controllers\IncidentController::class, 'testConnectionWithoutSave'])->name('modules.incidents.email.config.test.without.save');
        Route::put('/email-config/{id}', [App\Http\Controllers\IncidentController::class, 'updateEmailConfig'])->name('modules.incidents.email.config.update');
        Route::delete('/email-config/{id}', [App\Http\Controllers\IncidentController::class, 'deleteEmailConfig'])->name('modules.incidents.email.config.delete');
        Route::post('/email-config/{id}/test', [App\Http\Controllers\IncidentController::class, 'testConnection'])->name('modules.incidents.email.config.test');
        Route::post('/email-config/{id}/toggle-status', [App\Http\Controllers\IncidentController::class, 'toggleEmailConfigStatus'])->name('modules.incidents.email.config.toggle');
        Route::get('/email-config/{id}/live-emails', [App\Http\Controllers\IncidentController::class, 'fetchLiveEmails'])->name('modules.incidents.email.config.live.emails');
        
        // Dynamic routes (must come last)
        Route::get('/{id}', [App\Http\Controllers\IncidentController::class, 'show'])->name('modules.incidents.show');
        Route::put('/{id}', [App\Http\Controllers\IncidentController::class, 'update'])->name('modules.incidents.update');
        Route::post('/{id}/assign', [App\Http\Controllers\IncidentController::class, 'assign'])->name('modules.incidents.assign');
        Route::post('/{id}/status', [App\Http\Controllers\IncidentController::class, 'updateStatus'])->name('modules.incidents.status.update');
        Route::post('/{id}/comment', [App\Http\Controllers\IncidentController::class, 'addComment'])->name('modules.incidents.comment');
        Route::get('/{id}/timeline', [App\Http\Controllers\IncidentController::class, 'getTimeline'])->name('modules.incidents.timeline');
    });
    
    // Assets Management (HR/Accountant)
    Route::middleware('role:HR Officer,Accountant,System Admin')->group(function(){
        Route::get('/modules/assets', [App\Http\Controllers\AssetsController::class, 'index'])->name('modules.assets');
        Route::post('/assets/categories', [App\Http\Controllers\AssetsController::class, 'storeCategory'])->name('assets.categories.store');
        Route::post('/assets', [App\Http\Controllers\AssetsController::class, 'storeAsset'])->name('assets.store');
        Route::post('/assets/{asset}/assign', [App\Http\Controllers\AssetsController::class, 'assign'])->name('assets.assign');
        Route::post('/asset-assignments/{assignment}/return', [App\Http\Controllers\AssetsController::class, 'return'])->name('assets.return');
    });
    
    // Accounting Module Routes (Accountant, System Admin, CEO)
    Route::middleware('role:Accountant,System Admin,CEO')->prefix('modules/accounting')->name('modules.accounting.')->group(function(){
        // Dashboard
        Route::get('/', [AccountingController::class, 'index'])->name('index');
        Route::post('/dashboard/data', [AccountingController::class, 'getAccountingDashboardData'])->name('dashboard.data');
        
        // Chart of Accounts
        Route::get('/chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('chart-of-accounts');
        Route::get('/chart-of-accounts/{id}', [AccountingController::class, 'showAccount'])->name('accounts.show');
        Route::get('/chart-of-accounts/{id}/transactions', [AccountingController::class, 'getAccountTransactions'])->name('accounts.transactions');
        Route::get('/chart-of-accounts/{id}/balance-trend', [AccountingController::class, 'getAccountBalanceTrend'])->name('accounts.balance-trend');
        Route::post('/chart-of-accounts', [AccountingController::class, 'storeAccount'])->name('accounts.store');
        Route::put('/chart-of-accounts/{id}', [AccountingController::class, 'updateAccount'])->name('accounts.update');
        Route::delete('/chart-of-accounts/{id}', [AccountingController::class, 'deleteAccount'])->name('accounts.delete');
        Route::post('/chart-of-accounts/bulk-operations', [AccountingController::class, 'bulkOperations'])->name('accounts.bulk-operations');
        
        // Journal Entries
        Route::get('/journal-entries', [AccountingController::class, 'journalEntries'])->name('journal-entries');
        Route::post('/journal-entries/data', [AccountingController::class, 'getJournalEntriesData'])->name('journal-entries.data');
        Route::get('/journal-entries/{id}', [AccountingController::class, 'showJournalEntry'])->name('journal-entries.show');
        Route::post('/journal-entries', [AccountingController::class, 'storeJournalEntry'])->name('journal-entries.store');
        Route::put('/journal-entries/{id}', [AccountingController::class, 'updateJournalEntry'])->name('journal-entries.update');
        Route::post('/journal-entries/{id}/post', [AccountingController::class, 'postJournalEntry'])->name('journal-entries.post');
        
        // General Ledger
        Route::get('/general-ledger', [AccountingController::class, 'generalLedger'])->name('general-ledger');
        Route::post('/general-ledger/data', [AccountingController::class, 'getGeneralLedgerData'])->name('general-ledger.data');
        Route::get('/general-ledger/pdf', [AccountingController::class, 'generalLedger'])->name('general-ledger.pdf');
        
        // Financial Reports
        Route::get('/trial-balance', [AccountingController::class, 'trialBalance'])->name('trial-balance');
        Route::post('/trial-balance/data', [AccountingController::class, 'getTrialBalanceData'])->name('trial-balance.data');
        Route::get('/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('balance-sheet');
        Route::post('/balance-sheet/data', [AccountingController::class, 'getBalanceSheetData'])->name('balance-sheet.data');
        Route::get('/income-statement', [AccountingController::class, 'incomeStatement'])->name('income-statement');
        Route::post('/income-statement/data', [AccountingController::class, 'getIncomeStatementData'])->name('income-statement.data');
        
        // Accounts Payable
        Route::get('/accounts-payable/vendors', [AccountsPayableController::class, 'vendors'])->name('ap.vendors');
        Route::post('/accounts-payable/vendors/data', [AccountsPayableController::class, 'getVendorsData'])->name('ap.vendors.data');
        Route::get('/accounts-payable/vendors/{id}', [AccountsPayableController::class, 'showVendor'])->name('ap.vendors.show');
        Route::post('/accounts-payable/vendors', [AccountsPayableController::class, 'storeVendor'])->name('ap.vendors.store');
        Route::put('/accounts-payable/vendors/{id}', [AccountsPayableController::class, 'updateVendor'])->name('ap.vendors.update');
        Route::get('/accounts-payable/bills', [AccountsPayableController::class, 'bills'])->name('ap.bills');
        Route::post('/accounts-payable/bills/data', [AccountsPayableController::class, 'getBillsData'])->name('ap.bills.data');
        Route::get('/accounts-payable/bills/{id}', [AccountsPayableController::class, 'showBill'])->name('ap.bills.show');
        Route::get('/accounts-payable/bills/{id}/pdf', [AccountsPayableController::class, 'exportBillPdf'])->name('ap.bills.pdf');
        Route::post('/accounts-payable/bills', [AccountsPayableController::class, 'storeBill'])->name('ap.bills.store');
        Route::put('/accounts-payable/bills/{id}', [AccountsPayableController::class, 'updateBill'])->name('ap.bills.update');
        Route::get('/accounts-payable/payments', [AccountsPayableController::class, 'billPayments'])->name('ap.payments');
        Route::post('/accounts-payable/payments/data', [AccountsPayableController::class, 'getPaymentsData'])->name('ap.payments.data');
        Route::get('/accounts-payable/payments/{id}', [AccountsPayableController::class, 'showPayment'])->name('ap.payments.show');
        Route::get('/accounts-payable/payments/{id}/pdf', [AccountsPayableController::class, 'exportPaymentPdf'])->name('ap.payments.pdf');
        Route::post('/accounts-payable/payments', [AccountsPayableController::class, 'storeBillPayment'])->name('ap.payments.store');
        Route::get('/accounts-payable/aging-report', [AccountsPayableController::class, 'agingReport'])->name('ap.aging-report');
        
        // Accounts Receivable
        Route::get('/accounts-receivable/customers', [AccountsReceivableController::class, 'customers'])->name('ar.customers');
        Route::post('/accounts-receivable/customers', [AccountsReceivableController::class, 'storeCustomer'])->name('ar.customers.store');
        Route::get('/accounts-receivable/customers/{id}', [AccountsReceivableController::class, 'showCustomer'])->name('ar.customers.show');
        Route::post('/accounts-receivable/customers/{id}', [AccountsReceivableController::class, 'updateCustomer'])->name('ar.customers.update');
        Route::put('/accounts-receivable/customers/{id}', [AccountsReceivableController::class, 'updateCustomer'])->name('ar.customers.update.put');
        Route::get('/accounts-receivable/invoices', [AccountsReceivableController::class, 'invoices'])->name('ar.invoices');
        Route::post('/accounts-receivable/invoices/data', [AccountsReceivableController::class, 'getInvoicesData'])->name('ar.invoices.data');
        Route::get('/accounts-receivable/invoices/{id}', [AccountsReceivableController::class, 'showInvoice'])->name('ar.invoices.show');
        Route::get('/accounts-receivable/invoices/{id}/pdf', [AccountsReceivableController::class, 'exportInvoicePdf'])->name('ar.invoices.pdf');
        Route::post('/accounts-receivable/invoices', [AccountsReceivableController::class, 'storeInvoice'])->name('ar.invoices.store');
        Route::put('/accounts-receivable/invoices/{id}', [AccountsReceivableController::class, 'updateInvoice'])->name('ar.invoices.update');
        Route::post('/accounts-receivable/invoices/{id}/approve', [AccountsReceivableController::class, 'approveInvoice'])->name('ar.invoices.approve');
        Route::post('/accounts-receivable/invoices/{id}/reject', [AccountsReceivableController::class, 'rejectInvoice'])->name('ar.invoices.reject');
        Route::get('/accounts-receivable/payments', [AccountsReceivableController::class, 'invoicePayments'])->name('ar.payments');
        Route::post('/accounts-receivable/payments/data', [AccountsReceivableController::class, 'getPaymentsData'])->name('ar.payments.data');
        Route::get('/accounts-receivable/payments/{id}', [AccountsReceivableController::class, 'showInvoicePayment'])->name('ar.payments.show');
        Route::get('/accounts-receivable/payments/{id}/pdf', [AccountsReceivableController::class, 'exportInvoicePaymentPdf'])->name('ar.payments.pdf');
        Route::post('/accounts-receivable/payments', [AccountsReceivableController::class, 'storeInvoicePayment'])->name('ar.payments.store');
        Route::get('/accounts-receivable/credit-memos', [AccountsReceivableController::class, 'creditMemos'])->name('ar.credit-memos');
        Route::post('/accounts-receivable/credit-memos/data', [AccountsReceivableController::class, 'getCreditMemosData'])->name('ar.credit-memos.data');
        Route::get('/accounts-receivable/credit-memos/{id}', [AccountsReceivableController::class, 'showCreditMemo'])->name('ar.credit-memos.show');
        Route::get('/accounts-receivable/credit-memos/{id}/pdf', [AccountsReceivableController::class, 'exportCreditMemoPdf'])->name('ar.credit-memos.pdf');
        Route::post('/accounts-receivable/credit-memos', [AccountsReceivableController::class, 'storeCreditMemo'])->name('ar.credit-memos.store');
        Route::put('/accounts-receivable/credit-memos/{id}', [AccountsReceivableController::class, 'updateCreditMemo'])->name('ar.credit-memos.update');
        Route::post('/accounts-receivable/credit-memos/{id}/approve', [AccountsReceivableController::class, 'approveCreditMemo'])->name('ar.credit-memos.approve');
        Route::post('/accounts-receivable/credit-memos/{id}/reject', [AccountsReceivableController::class, 'rejectCreditMemo'])->name('ar.credit-memos.reject');
        Route::get('/accounts-receivable/aging-report', [AccountsReceivableController::class, 'agingReport'])->name('ar.aging-report');
        
        // Budgeting & Forecasting
        Route::get('/budgeting/budgets', [BudgetingController::class, 'budgets'])->name('budgeting.budgets');
        Route::post('/budgeting/budgets/data', [BudgetingController::class, 'getBudgetsData'])->name('budgeting.budgets.data');
        Route::post('/budgeting/budgets', [BudgetingController::class, 'store'])->name('budgeting.budgets.store');
        Route::get('/budgeting/budgets/{id}', [BudgetingController::class, 'show'])->name('budgeting.budgets.show');
        Route::post('/budgeting/budgets/{id}', [BudgetingController::class, 'update'])->name('budgeting.budgets.update');
        Route::put('/budgeting/budgets/{id}', [BudgetingController::class, 'update'])->name('budgeting.budgets.update.put');
        Route::delete('/budgeting/budgets/{id}', [BudgetingController::class, 'destroy'])->name('budgeting.budgets.destroy');
        Route::post('/budgeting/budgets/{id}/update-actuals', [BudgetingController::class, 'updateActuals'])->name('budgeting.budgets.update-actuals');
        Route::post('/budgeting/budgets/{id}/approve', [BudgetingController::class, 'approve'])->name('budgeting.budgets.approve');
        Route::get('/budgeting/budget-reports', [BudgetingController::class, 'budgetReports'])->name('budgeting.budget-reports');
        Route::post('/budgeting/budget-reports/data', [BudgetingController::class, 'getBudgetReportsData'])->name('budgeting.budget-reports.data');
        Route::get('/budgeting/forecasting', [BudgetingController::class, 'forecasting'])->name('budgeting.forecasting');
        Route::post('/budgeting/forecasting/data', [BudgetingController::class, 'getForecastingData'])->name('budgeting.forecasting.data');
        
        // Legacy budget routes (keeping for backward compatibility)
        Route::get('/budgeting', [BudgetController::class, 'index'])->name('budgeting.index');
        Route::post('/budgeting', [BudgetController::class, 'store'])->name('budgeting.store');
        Route::post('/budgeting/{id}/update-actuals', [BudgetController::class, 'updateActuals'])->name('budgeting.update-actuals');
        Route::post('/budgeting/{id}/approve', [BudgetController::class, 'approve'])->name('budgeting.approve');
        
        // Cash & Bank Management
        Route::get('/cash-bank/accounts', [CashBankController::class, 'bankAccounts'])->name('cash-bank.accounts');
        Route::post('/cash-bank/accounts', [CashBankController::class, 'store'])->name('cash-bank.accounts.store');
        Route::put('/cash-bank/accounts/{id}', [CashBankController::class, 'update'])->name('cash-bank.accounts.update')->where('id', '[0-9]+');
        Route::delete('/cash-bank/accounts/{id}', [CashBankController::class, 'destroy'])->name('cash-bank.accounts.destroy')->where('id', '[0-9]+');
        Route::post('/cash-bank/accounts/data', [CashBankController::class, 'getBankAccountsData'])->name('cash-bank.accounts.data');
        Route::get('/cash-bank/reconciliation', [CashBankController::class, 'reconciliation'])->name('cash-bank.reconciliation');
        Route::post('/cash-bank/reconciliation/data', [CashBankController::class, 'getReconciliationData'])->name('cash-bank.reconciliation.data');
        Route::get('/cash-bank/cash-flow', [CashBankController::class, 'cashFlowStatement'])->name('cash-bank.cash-flow');
        Route::post('/cash-bank/cash-flow/data', [CashBankController::class, 'getCashFlowData'])->name('cash-bank.cash-flow.data');
        
        // Taxation
        Route::get('/taxation', [TaxController::class, 'index'])->name('taxation.index');
        Route::post('/taxation/data', [TaxController::class, 'getTaxSettingsData'])->name('taxation.data');
        Route::post('/taxation', [TaxController::class, 'store'])->name('taxation.store');
        Route::put('/taxation/{id}', [TaxController::class, 'update'])->name('taxation.update');
        Route::delete('/taxation/{id}', [TaxController::class, 'destroy'])->name('taxation.destroy');
        Route::get('/taxation/reports', [TaxController::class, 'reports'])->name('taxation.reports');
        Route::post('/taxation/reports/data', [TaxController::class, 'getTaxReportsData'])->name('taxation.reports.data');
        Route::get('/taxation/reports/export', [TaxController::class, 'exportReport'])->name('taxation.reports.export');
        Route::get('/taxation/paye-management', [TaxController::class, 'payeManagement'])->name('taxation.paye-management');
        Route::post('/taxation/paye-management/data', [TaxController::class, 'getPayeManagementData'])->name('taxation.paye-management.data');
        Route::post('/taxation/paye-calculation', [TaxController::class, 'getPayeCalculation'])->name('taxation.paye-calculation');
        
        // Fixed Assets Management
        Route::prefix('fixed-assets')->name('fixed-assets.')->group(function(){
            // Specific routes first (before parameterized routes)
            Route::get('/depreciation/list', [FixedAssetController::class, 'depreciation'])->name('depreciation');
            Route::post('/depreciation/calculate', [FixedAssetController::class, 'calculateDepreciation'])->name('calculate-depreciation');
            Route::post('/depreciation/{id}/post', [FixedAssetController::class, 'postDepreciation'])->name('post-depreciation');
            Route::get('/depreciation/schedule', [FixedAssetController::class, 'depreciationSchedule'])->name('depreciation-schedule');
            Route::get('/depreciation/schedule/{assetId}', [FixedAssetController::class, 'depreciationSchedule'])->name('depreciation-schedule.asset');
            Route::get('/reports', [FixedAssetController::class, 'reports'])->name('reports');
            
            // Category Management
            Route::get('/categories', [FixedAssetController::class, 'categories'])->name('categories');
            Route::get('/categories/{id}', [FixedAssetController::class, 'getCategory'])->name('categories.show');
            Route::post('/categories', [FixedAssetController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{id}', [FixedAssetController::class, 'updateCategory'])->name('categories.update');
            Route::delete('/categories/{id}', [FixedAssetController::class, 'destroyCategory'])->name('categories.destroy');
            
            // Barcode routes
            Route::get('/barcodes/scan', [FixedAssetController::class, 'scanBarcode'])->name('scan-barcode');
            Route::post('/{id}/generate-barcode', [FixedAssetController::class, 'generateBarcode'])->name('generate-barcode');
            Route::get('/{id}/print-barcode', [FixedAssetController::class, 'printBarcode'])->name('print-barcode');
            Route::get('/barcodes/print', [FixedAssetController::class, 'printBarcodes'])->name('print-barcodes');
            Route::post('/barcodes/bulk-generate', [FixedAssetController::class, 'bulkGenerateBarcodes'])->name('bulk-generate-barcodes');
            
            // CRUD routes (parameterized routes last)
            Route::get('/', [FixedAssetController::class, 'index'])->name('index');
            Route::get('/create', [FixedAssetController::class, 'create'])->name('create');
            Route::post('/', [FixedAssetController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [FixedAssetController::class, 'edit'])->name('edit');
            Route::put('/{id}', [FixedAssetController::class, 'update'])->name('update');
            Route::delete('/{id}', [FixedAssetController::class, 'destroy'])->name('destroy');
            Route::get('/{id}', [FixedAssetController::class, 'show'])->name('show');
        });
    });
    
    // Reporting Routes
    Route::view('/modules/reports', 'modules.reports.index')->name('modules.reports');
    
    // Admin Routes - Advanced CRUD Operations
    Route::prefix('admin')->middleware('role:System Admin')->group(function () {
        // System Status and Health
        Route::get('system', [SystemController::class, 'index'])->name('admin.system');
        Route::get('system/health', [SystemController::class, 'healthCheck'])->name('admin.system.health');
        Route::get('system/live-stats', [SystemController::class, 'getLiveStats'])->name('admin.system.live-stats');
        
        // System Errors
        Route::get('system/errors', [\App\Http\Controllers\Admin\SystemErrorsController::class, 'index'])->name('admin.system.errors');
        Route::get('system/errors/statistics', [\App\Http\Controllers\Admin\SystemErrorsController::class, 'getStatistics'])->name('admin.system.errors.statistics');
        Route::post('system/errors/clear', [\App\Http\Controllers\Admin\SystemErrorsController::class, 'clearLogs'])->name('admin.system.errors.clear');
        Route::get('system/errors/download', [\App\Http\Controllers\Admin\SystemErrorsController::class, 'downloadLogs'])->name('admin.system.errors.download');
        // System download/backup endpoints
        Route::post('system/backup-now', [SystemController::class, 'backupNow'])->name('admin.system.backup.now');
        Route::get('system/backup/list', [SystemController::class, 'listBackups'])->name('admin.system.backup.list');
        Route::get('system/backup/download/{file}', [SystemController::class, 'downloadBackup'])->name('admin.system.backup.download');
        Route::post('system/backup/schedule', [SystemController::class, 'updateBackupSchedule'])->name('admin.system.backup.schedule');
        Route::delete('system/backup/{backup}', [SystemController::class, 'deleteBackup'])->name('admin.system.backup.delete');
        // System User Management
        Route::post('system/users', [SystemController::class, 'getUsers'])->name('admin.system.users');
        Route::post('system/users/{user}/block', [SystemController::class, 'blockUser'])->name('admin.system.users.block');
        Route::post('system/users/{user}/unblock', [SystemController::class, 'unblockUser'])->name('admin.system.users.unblock');
        Route::delete('system/users/{user}', [SystemController::class, 'deleteUser'])->name('admin.system.users.delete');
        // Session Management
        Route::get('system/sessions', [SystemController::class, 'getActiveSessions'])->name('admin.system.sessions');
        Route::delete('system/sessions/{sessionId}', [SystemController::class, 'revokeSession'])->name('admin.system.sessions.revoke');
        Route::post('system/users/{user}/revoke-sessions', [SystemController::class, 'revokeAllUserSessions'])->name('admin.system.users.revoke-sessions');
        Route::post('system/sessions/revoke-all', [SystemController::class, 'revokeAllSessions'])->name('admin.system.sessions.revoke-all');
        // User Management
        // User export route must be BEFORE resource route to avoid conflicts
        Route::get('users/export', [UserController::class, 'export'])->name('admin.users.export');
        Route::get('users/statistics', [UserController::class, 'statistics'])->name('admin.users.statistics');
        
        Route::resource('users', UserController::class)->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'show' => 'admin.users.show',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::post('users/{user}/send-password-reset-sms', [UserController::class, 'sendPasswordResetSMS'])->name('admin.users.send-password-reset-sms');
        Route::post('users/bulk-activate', [UserController::class, 'bulkActivate'])->name('admin.users.bulk-activate');
        Route::post('users/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('admin.users.bulk-deactivate');
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('admin.users.bulk-delete');
        Route::post('users/{user}/refresh-email-verification', [UserController::class, 'refreshEmailVerification'])->name('admin.users.refresh-email-verification');
        Route::post('users/bulk-refresh-email-verification', [UserController::class, 'bulkRefreshEmailVerification'])->name('admin.users.bulk-refresh-email-verification');
        
        // Role Management
        Route::get('roles', [RoleController::class, 'index'])->name('admin.roles');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
        
        // Permission Management (Admin)
        Route::name('admin.permissions.')->group(function(){
            Route::get('permissions', [App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('index');
            Route::get('permissions/{permission}', [App\Http\Controllers\Admin\PermissionController::class, 'show'])->name('show');
            Route::post('permissions', [App\Http\Controllers\Admin\PermissionController::class, 'store'])->name('store');
            Route::put('permissions/{permission}', [App\Http\Controllers\Admin\PermissionController::class, 'update'])->name('update');
            Route::delete('permissions/{permission}', [App\Http\Controllers\Admin\PermissionController::class, 'destroy'])->name('destroy');
            Route::post('permissions/{permission}/toggle-status', [App\Http\Controllers\Admin\PermissionController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('permissions/{permission}/assign-roles', [App\Http\Controllers\Admin\PermissionController::class, 'assignToRoles'])->name('assign-roles');
            Route::get('permissions/{permission}/roles', [App\Http\Controllers\Admin\PermissionController::class, 'getPermissionRoles'])->name('get-roles');
            Route::post('permissions/bulk-activate', [App\Http\Controllers\Admin\PermissionController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('permissions/bulk-deactivate', [App\Http\Controllers\Admin\PermissionController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::post('permissions/bulk-delete', [App\Http\Controllers\Admin\PermissionController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('permissions/export/csv', [App\Http\Controllers\Admin\PermissionController::class, 'export'])->name('export');
            Route::get('permissions/statistics', [App\Http\Controllers\Admin\PermissionController::class, 'statistics'])->name('statistics');
        });
        
        // Settings Management
        Route::get('settings', [SettingsController::class, 'index'])->name('admin.settings');
        Route::get('settings/organization', [SettingsController::class, 'organizationSettings'])->name('admin.settings.organization');
        Route::get('settings/system', [SettingsController::class, 'systemSettings'])->name('admin.settings.system.page');
        Route::get('settings/communication', [SettingsController::class, 'communicationSettings'])->name('admin.settings.communication.page');
        Route::get('settings/profile', [SettingsController::class, 'profileSettings'])->name('admin.settings.profile.page');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('settings/data', [SettingsController::class, 'getSettings'])->name('settings.data');
        Route::post('settings/upload-logo', [SettingsController::class, 'uploadLogo'])->name('settings.upload-logo');
        Route::post('settings/financial-year/toggle-lock', [SettingsController::class, 'toggleFinancialYearLock'])->name('admin.settings.fy.toggle-lock');
        Route::post('settings/financial-year/initialize', [SettingsController::class, 'initializeFinancialYear'])->name('admin.settings.fy.initialize');
        
        // Individual Organization Settings Pages
        Route::get('settings/organization/info', [SettingsController::class, 'organizationInfo'])->name('admin.settings.organization.info');
        Route::get('settings/organization/financial-year', [SettingsController::class, 'financialYear'])->name('admin.settings.organization.financial-year');
        Route::get('settings/organization/currency', [SettingsController::class, 'currency'])->name('admin.settings.organization.currency');
        
        // Admin Profile
        Route::post('settings/profile', [SettingsController::class, 'updateAdminProfile'])->name('admin.settings.profile');
        Route::post('settings/profile/photo', [SettingsController::class, 'updateAdminPhoto'])->name('admin.settings.profile.photo');
        
        // System Settings CRUD (API endpoints)
        Route::get('settings/system/data', [SettingsController::class, 'getSystemSettings'])->name('admin.settings.system');
        Route::post('settings/system', [SettingsController::class, 'storeSystemSetting'])->name('admin.settings.system.store');
        Route::put('settings/system/{id}', [SettingsController::class, 'updateSystemSetting'])->name('admin.settings.system.update');
        Route::delete('settings/system/{id}', [SettingsController::class, 'deleteSystemSetting'])->name('admin.settings.system.delete');
        
        // Communication Settings (SMS & Email) - API endpoints
        Route::get('settings/communication/data', [SettingsController::class, 'getCommunicationSettings'])->name('admin.settings.communication.data');
        Route::post('settings/communication', [SettingsController::class, 'updateCommunicationSettings'])->name('admin.settings.communication.update');
        Route::post('settings/communication/test-sms', [SettingsController::class, 'testSMS'])->name('admin.settings.communication.test-sms');
        Route::post('settings/communication/test-email', [SettingsController::class, 'testEmail'])->name('admin.settings.communication.test-email');
        Route::get('settings/communication/check-email', [SettingsController::class, 'checkEmailStatus'])->name('admin.settings.communication.check-email');
        Route::get('settings/communication/check-sms', [SettingsController::class, 'checkSMSStatus'])->name('admin.settings.communication.check-sms');
        
        // Notification Providers Management
        Route::get('settings/notification-providers', [SettingsController::class, 'getNotificationProviders'])->name('admin.settings.notification-providers');
        Route::get('settings/notification-providers/{provider}', [SettingsController::class, 'getNotificationProvider'])->name('admin.settings.notification-providers.show');
        Route::post('settings/notification-providers', [SettingsController::class, 'storeNotificationProvider'])->name('admin.settings.notification-providers.store');
        Route::put('settings/notification-providers/{provider}', [SettingsController::class, 'updateNotificationProvider'])->name('admin.settings.notification-providers.update');
        Route::delete('settings/notification-providers/{provider}', [SettingsController::class, 'deleteNotificationProvider'])->name('admin.settings.notification-providers.delete');
        Route::post('settings/notification-providers/{provider}/set-primary', [SettingsController::class, 'setPrimaryProvider'])->name('admin.settings.notification-providers.set-primary');
        Route::post('settings/notification-providers/{provider}/test', [SettingsController::class, 'testNotificationProvider'])->name('admin.settings.notification-providers.test');
        
        // Activity Log
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log');
        Route::get('activity-log/data', [ActivityLogController::class, 'getActivityData'])->name('activity-log.data');
        Route::get('activity-log/statistics', [ActivityLogController::class, 'getStatistics'])->name('activity-log.statistics');
        Route::get('activity-log/export', [ActivityLogController::class, 'export'])->name('activity-log.export');
    });
});
