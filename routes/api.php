<?php

use App\Http\Controllers\CustomerDataController;
use App\Http\Controllers\CustomerDataControllerPostpaid;
use App\Http\Controllers\CustomerDataControllerPrepaid;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
// use App\Http\Controllers\Auth\PasswordResetController;
// use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\TestMailController;
use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\CustomerDataControllerPrepaid;
use App\Http\Controllers\KycDashboardController;
use App\Http\Controllers\KycApprovalController;


use App\Http\Controllers\AdminViewController;
// use App\Http\Controllers\AdminController;

use App\Http\Controllers\API\AdminController;


use App\Http\Controllers\KycSubmissionController;

use App\Http\Controllers\RicoController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\AuditController;



use App\Http\Controllers\AuthController;
use App\Http\Controllers\KycController;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/submit-kyc', [KycController::class, 'submit']);
Route::get('/kyc-by-role', [KycController::class, 'getByRole']);
Route::post('/approve-kyc', [KycController::class, 'approve']);











/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 



// Route::post('kyc/submit', [KycSubmissionController::class, 'store']);

// Route::get('/customer-data-prepaid', [CustomerDataControllerPrepaid::class, 'fetchByMeterNo']);
Route::post('/get_customerdata', [CustomerDataController::class, 'fetchCustomerData']);

// Route::post('/update_identification', [CustomerDataController::class, 'updateIdentification']);

Route::post('/updateIdentification', [CustomerDataController::class, 'updateIdentification']);



// Route::post('register',        [RegistrationController::class, 'register']);

// Route::post('login',           [LoginController::class, 'login']);





// // Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// // Route::post('/login', [LoginController::class, 'loginWeb'])->name('login.submit');
// // Route::post('/logout', [LoginController::class, 'logoutWeb'])->name('logout');


// // PROTECTED ROUTES
// Route::middleware('auth:api')->group(function () {
//     // Logout
//     Route::post('logout', [LoginController::class, 'logout']);

//     Route::get('/dashboard/customers', [DashboardController::class, 'totalCustomers']);

//     // Route::get('/send-test-email', [TestMailController::class, 'sendTestEmail']);


//     // Agencies
//     // Route::apiResource('agencies', AgencyController::class);


//     // Roles & Permissions

// Route::middleware('auth:api')->group(function () {
//     Route::post('/roles', [RolePermissionController::class, 'createRole']);
//     Route::get('/roles', [RolePermissionController::class, 'listRoles']);
//     Route::put('/roles/{id}', [RolePermissionController::class, 'updateRole']);
//     Route::delete('/roles/{id}', [RolePermissionController::class, 'deleteRole']);

//     Route::post('/permissions', [RolePermissionController::class, 'createPermission']);
//     Route::get('/permissions', [RolePermissionController::class, 'listPermissions']);
//     Route::delete('/permissions/{id}', [RolePermissionController::class, 'deletePermission']);

//     Route::post('/assign-role', [RolePermissionController::class, 'assignRole']);
//     Route::post('/assign-perms', [RolePermissionController::class, 'assignPermissionsToRole']);
// });





// Route::middleware(['auth:sanctum'])->group(function () {
//     // Route::get('/dashboard', [DashboardController::class, 'index']);
//     Route::post('/dashboard/{id}/approve', [DashboardController::class, 'approve']);
//     Route::post('/dashboard/{id}/reject', [DashboardController::class, 'reject']);
// });



// Route::middleware('auth:sanctum')->get('/dashboard', [DashboardController::class, 'index']);


// Route::middleware('auth:sanctum')->post('/kyc/{id}/status', [KycApprovalController::class, 'updateStatus']);




// // //  Route::middleware(['auth:api', 'ensureAdmin'])->group(function () {
// // //    Route::middleware(['auth:api', 'ensureAdmin'])->group(function () {
// //     Route::middleware('auth:api')->group(function () {

// //     Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
// //     Route::get('/admin/kyc-submissions', [AdminController::class, 'listKycForms']);
// //     Route::post('/admin/kyc/{id}/approve', [AdminController::class, 'approveKyc']);
// //     Route::post('/admin/kyc/{id}/reject', [AdminController::class, 'rejectKyc']);

// //     Route::middleware(['auth:sanctum'])->group(function () {
// //     Route::prefix('dashboard')->group(function () {
// //         Route::get('/admin', [AdminController::class, 'dashboard'])->middleware('ensure.admin');
// //         Route::get('/billing', [BillingController::class, 'dashboard'])->middleware('ensure.billing');
// //         Route::get('/customer-care', [CustomerCareController::class, 'dashboard'])->middleware('ensure.customerCare');
// //         Route::get('/audit', [AuditController::class, 'dashboard'])->middleware('ensure.audit');
// //     });
// // });


// Route::prefix('dashboard')->group(function () {
//     Route::get('{role}', [KycDashboardController::class, 'index']);
//     Route::post('{role}/{id}/status', [KycDashboardController::class, 'updateStatus']);
// });


    
// //     Route::get('/admin/users', [AdminController::class, 'listUsers']);
// //     Route::post('/admin/users/create', [AdminController::class, 'createUser']);
// //     Route::put('/admin/users/{id}', [AdminController::class, 'updateUser']);
// //     Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);

// //     Route::get('/admin/audit-logs', [AdminController::class, 'auditLogs']);
// // });

//     // Assignments
//     Route::post('assign-role',        [RolePermissionController::class, 'assignRole'])->middleware('permission:user.assignRole');
//     Route::post('assign-permissions', [RolePermissionController::class, 'assignPermissionsToRole'])->middleware('permission:role.assignPermissions');

//     // Audit Logs
//     Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit.view');


// // Routes: routes/api.php (inside auth:api)
// Route::middleware('permission:dashboard.view')->get('dashboard/metrics', [DashboardController::class, 'metrics']);









// });
