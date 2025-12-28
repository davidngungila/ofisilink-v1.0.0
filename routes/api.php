<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\FileApiController;
use App\Http\Controllers\Api\FinanceApiController;
use App\Http\Controllers\Api\HrApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\DeviceTokenApiController;

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| All routes for mobile application access. All routes require authentication
| via Bearer token except login and password reset endpoints.
|
*/

// Public routes (no authentication required)
Route::prefix('mobile/v1')->group(function () {
    // Authentication
    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::post('/auth/login-otp', [AuthApiController::class, 'loginWithOtp']);
    Route::post('/auth/verify-otp', [AuthApiController::class, 'verifyOtp']);
    Route::post('/auth/resend-otp', [AuthApiController::class, 'resendOtp']);
    Route::post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthApiController::class, 'resetPassword']);
});

// Protected routes (authentication required)
Route::prefix('mobile/v1')->middleware('auth:sanctum')->group(function () {
    
    // Authentication
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::post('/auth/refresh', [AuthApiController::class, 'refresh']);
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    Route::put('/auth/change-password', [AuthApiController::class, 'changePassword']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
    Route::get('/dashboard/notifications', [DashboardApiController::class, 'notifications']);
    
    // Profile
    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::put('/profile', [ProfileApiController::class, 'update']);
    Route::post('/profile/photo', [ProfileApiController::class, 'updatePhoto']);
    
    // Users
    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/users/{id}', [UserApiController::class, 'show']);
    Route::get('/users/search', [UserApiController::class, 'search']);
    
    // Departments
    Route::get('/departments', [DepartmentApiController::class, 'index']);
    Route::get('/departments/{id}', [DepartmentApiController::class, 'show']);
    Route::get('/departments/{id}/members', [DepartmentApiController::class, 'members']);
    
    // Attendance
    Route::get('/attendance', [AttendanceApiController::class, 'index']);
    Route::get('/attendance/my', [AttendanceApiController::class, 'myAttendance']);
    Route::get('/attendance/{id}', [AttendanceApiController::class, 'show']);
    Route::get('/attendance/daily/{date}', [AttendanceApiController::class, 'daily']);
    Route::get('/attendance/summary', [AttendanceApiController::class, 'summary']);
    Route::post('/attendance/check-in', [AttendanceApiController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceApiController::class, 'checkOut']);
    
    // Leave Management
    Route::get('/leaves', [LeaveApiController::class, 'index']);
    Route::get('/leaves/my', [LeaveApiController::class, 'myLeaves']);
    Route::get('/leaves/pending', [LeaveApiController::class, 'pending']);
    Route::get('/leaves/{id}', [LeaveApiController::class, 'show']);
    Route::post('/leaves', [LeaveApiController::class, 'store']);
    Route::put('/leaves/{id}', [LeaveApiController::class, 'update']);
    Route::post('/leaves/{id}/cancel', [LeaveApiController::class, 'cancel']);
    Route::post('/leaves/{id}/approve', [LeaveApiController::class, 'approve']);
    Route::post('/leaves/{id}/reject', [LeaveApiController::class, 'reject']);
    Route::get('/leaves/balance', [LeaveApiController::class, 'balance']);
    Route::get('/leaves/types', [LeaveApiController::class, 'types']);
    
    // Task Management
    Route::get('/tasks', [TaskApiController::class, 'index']);
    Route::get('/tasks/my', [TaskApiController::class, 'myTasks']);
    Route::get('/tasks/assigned', [TaskApiController::class, 'assigned']);
    Route::get('/tasks/{id}', [TaskApiController::class, 'show']);
    Route::post('/tasks', [TaskApiController::class, 'store']);
    Route::put('/tasks/{id}', [TaskApiController::class, 'update']);
    Route::post('/tasks/{id}/complete', [TaskApiController::class, 'complete']);
    Route::post('/tasks/{id}/assign', [TaskApiController::class, 'assign']);
    Route::get('/tasks/{id}/activities', [TaskApiController::class, 'activities']);
    Route::post('/tasks/{id}/activities', [TaskApiController::class, 'storeActivity']);
    Route::post('/tasks/{id}/activities/{activityId}/complete', [TaskApiController::class, 'completeActivity']);
    Route::post('/tasks/{id}/activities/{activityId}/report', [TaskApiController::class, 'submitReport']);
    
    // File Management - Digital Files
    Route::get('/files/digital', [FileApiController::class, 'digitalIndex']);
    Route::get('/files/digital/folders', [FileApiController::class, 'digitalFolders']);
    Route::get('/files/digital/folders/{id}', [FileApiController::class, 'digitalFolderContents']);
    Route::get('/files/digital/{id}', [FileApiController::class, 'digitalShow']);
    Route::post('/files/digital/upload', [FileApiController::class, 'digitalUpload']);
    Route::get('/files/digital/{id}/download', [FileApiController::class, 'digitalDownload']);
    Route::post('/files/digital/{id}/request-access', [FileApiController::class, 'requestAccess']);
    Route::get('/files/digital/my-requests', [FileApiController::class, 'myAccessRequests']);
    Route::get('/files/digital/pending-requests', [FileApiController::class, 'pendingAccessRequests']);
    Route::post('/files/digital/requests/{id}/approve', [FileApiController::class, 'approveAccessRequest']);
    Route::post('/files/digital/requests/{id}/reject', [FileApiController::class, 'rejectAccessRequest']);
    Route::get('/files/digital/search', [FileApiController::class, 'digitalSearch']);
    
    // File Management - Physical Racks
    Route::get('/files/physical', [FileApiController::class, 'physicalIndex']);
    Route::get('/files/physical/categories', [FileApiController::class, 'physicalCategories']);
    Route::get('/files/physical/racks/{id}', [FileApiController::class, 'physicalRackContents']);
    Route::get('/files/physical/{id}', [FileApiController::class, 'physicalShow']);
    Route::post('/files/physical/{id}/request', [FileApiController::class, 'requestPhysicalFile']);
    Route::get('/files/physical/my-requests', [FileApiController::class, 'myPhysicalRequests']);
    Route::get('/files/physical/pending-requests', [FileApiController::class, 'pendingPhysicalRequests']);
    Route::post('/files/physical/requests/{id}/approve', [FileApiController::class, 'approvePhysicalRequest']);
    Route::post('/files/physical/requests/{id}/reject', [FileApiController::class, 'rejectPhysicalRequest']);
    Route::post('/files/physical/requests/{id}/return', [FileApiController::class, 'returnPhysicalFile']);
    
    // Finance - Petty Cash
    Route::get('/finance/petty-cash/stats', [FinanceApiController::class, 'pettyCashStats']);
    Route::get('/finance/petty-cash', [FinanceApiController::class, 'pettyCashIndex']);
    Route::get('/finance/petty-cash/{id}', [FinanceApiController::class, 'pettyCashShow']);
    Route::post('/finance/petty-cash', [FinanceApiController::class, 'pettyCashStore']);
    Route::put('/finance/petty-cash/{id}', [FinanceApiController::class, 'pettyCashUpdate']);
    Route::post('/finance/petty-cash/{id}/approve', [FinanceApiController::class, 'pettyCashApprove']);
    Route::post('/finance/petty-cash/{id}/reject', [FinanceApiController::class, 'pettyCashReject']);
    
    // Finance - Imprest
    Route::get('/finance/imprest', [FinanceApiController::class, 'imprestIndex']);
    Route::get('/finance/imprest/{id}', [FinanceApiController::class, 'imprestShow']);
    Route::post('/finance/imprest', [FinanceApiController::class, 'imprestStore']);
    Route::post('/finance/imprest/{id}/approve', [FinanceApiController::class, 'imprestApprove']);
    Route::post('/finance/imprest/{id}/reject', [FinanceApiController::class, 'imprestReject']);
    Route::post('/finance/imprest/{id}/submit-receipt', [FinanceApiController::class, 'imprestSubmitReceipt']);
    
    // Finance - Payroll
    Route::get('/finance/payroll', [FinanceApiController::class, 'payrollIndex']);
    Route::get('/finance/payroll/my', [FinanceApiController::class, 'myPayroll']);
    Route::get('/finance/payroll/{id}', [FinanceApiController::class, 'payrollShow']);
    
    // HR - Permission Requests
    Route::get('/hr/permissions', [HrApiController::class, 'permissionIndex']);
    Route::get('/hr/permissions/my', [HrApiController::class, 'myPermissions']);
    Route::get('/hr/permissions/{id}', [HrApiController::class, 'permissionShow']);
    Route::post('/hr/permissions', [HrApiController::class, 'permissionStore']);
    Route::post('/hr/permissions/{id}/approve', [HrApiController::class, 'permissionApprove']);
    Route::post('/hr/permissions/{id}/reject', [HrApiController::class, 'permissionReject']);
    Route::post('/hr/permissions/{id}/confirm-return', [HrApiController::class, 'permissionConfirmReturn']);
    
    // HR - Sick Sheets
    Route::get('/hr/sick-sheets', [HrApiController::class, 'sickSheetIndex']);
    Route::get('/hr/sick-sheets/my', [HrApiController::class, 'mySickSheets']);
    Route::get('/hr/sick-sheets/{id}', [HrApiController::class, 'sickSheetShow']);
    Route::post('/hr/sick-sheets', [HrApiController::class, 'sickSheetStore']);
    Route::post('/hr/sick-sheets/{id}/approve', [HrApiController::class, 'sickSheetApprove']);
    Route::post('/hr/sick-sheets/{id}/reject', [HrApiController::class, 'sickSheetReject']);
    
    // HR - Assessments/Performance
    Route::get('/hr/assessments', [HrApiController::class, 'assessmentIndex']);
    Route::get('/hr/assessments/my', [HrApiController::class, 'myAssessments']);
    Route::get('/hr/assessments/{id}', [HrApiController::class, 'assessmentShow']);
    Route::post('/hr/assessments', [HrApiController::class, 'assessmentStore']);
    Route::post('/hr/assessments/{id}/progress', [HrApiController::class, 'assessmentSubmitProgress']);
    Route::get('/hr/assessments/{id}/progress', [HrApiController::class, 'assessmentProgress']);
    
    // HR - Recruitment
    Route::get('/hr/jobs', [HrApiController::class, 'jobIndex']);
    Route::get('/hr/jobs/{id}', [HrApiController::class, 'jobShow']);
    Route::get('/hr/jobs/{id}/applications', [HrApiController::class, 'jobApplications']);
    Route::post('/hr/jobs/{id}/apply', [HrApiController::class, 'jobApply']);
    
    // HR - Employees
    Route::get('/hr/employees', [HrApiController::class, 'employeeIndex']);
    Route::get('/hr/employees/{id}', [HrApiController::class, 'employeeShow']);
    
    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread', [NotificationApiController::class, 'unread']);
    Route::get('/notifications/{id}', [NotificationApiController::class, 'show']);
    Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllAsRead']);
    
    // Device Tokens (FCM Push Notifications)
    Route::post('/device/register', [DeviceTokenApiController::class, 'register']);
    Route::delete('/device/unregister', [DeviceTokenApiController::class, 'unregister']);
    Route::get('/device/tokens', [DeviceTokenApiController::class, 'tokens']);
    Route::put('/device/tokens/{id}', [DeviceTokenApiController::class, 'update']);
    
    // Incidents
    Route::get('/incidents', [HrApiController::class, 'incidentIndex']);
    Route::get('/incidents/my', [HrApiController::class, 'myIncidents']);
    Route::get('/incidents/{id}', [HrApiController::class, 'incidentShow']);
    Route::post('/incidents', [HrApiController::class, 'incidentStore']);
    Route::put('/incidents/{id}', [HrApiController::class, 'incidentUpdate']);
    Route::post('/incidents/{id}/update', [HrApiController::class, 'incidentAddUpdate']);
});



