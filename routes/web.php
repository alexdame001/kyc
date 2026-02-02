<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestMailController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AdminViewController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\RicoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RKAMController;
use App\Http\Controllers\BMController;
use App\Http\Controllers\CCUDashboardController;
use App\Http\Controllers\CCUController;
use App\Http\Controllers\CCUController2;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\StaffPasswordController;
use App\Http\Controllers\BulkMailController;






use App\Http\Controllers\ValidationController;


use App\http\Controllers\AgingAnalysisController;





use App\Http\Controllers\AdminController;

// use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Auth\RoleLoginController;


// Route::prefix('ccu')->middleware(['auth', 'role:ccu'])->group(function () {
//     Route::get('/dashboard', [CCUDashboardController::class, 'index'])->name('ccu.dashboard');
//     Route::get('/review/{id}', [CCUDashboardController::class, 'review'])->name('ccu.review');
//     Route::post('/review/{id}', [CCUDashboardController::class, 'submitReview'])->name('ccu.review.submit');
// });

use App\Http\Controllers\FirstLevelController;

Route::prefix('firstlevel')->group(function () {
    Route::get('/dashboard', [FirstLevelController::class, 'index'])->name('firstlevel.dashboard');
    Route::get('/list/{validatorType}', [FirstLevelController::class, 'list'])->name('firstlevel.list');
});



Route::post('/nin/lookup', [CustomerController::class, 'ninLookup'])->name('nin.lookup');

    Route::get('/admin/audit-logs', [AdminController::class, 'showAuditLogs'])->name('admin.audit.logs');


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [CCUController::class, 'index'])->name('ccu.dashboard');
    Route::get('/requests', [CCUController::class, 'viewRequests'])->name('ccu.requests');
    Route::get('/aging-analysis', [CCUController::class, 'agingAnalysis'])->name('ccu.aging');
        Route::get('/view/{id}', [CCUController::class, 'show'])->name('ccu.show');

        // Route::get('/aging-analysis/export', [AgingAnalysisController::class, 'export'])->name('aging.export');


});

// CCU Aging Analysis
Route::middleware(['auth'])->group(function () {
    Route::get('/ccu/aging-analysis', [App\Http\Controllers\CcuController::class, 'agingAnalysis'])->name('ccu.aging');
    Route::get('/ccu/aging-analysis/export', [App\Http\Controllers\CcuController::class, 'exportAgingAnalysis'])->name('ccu.aging.export');
    Route::get('/ccu/aging-analysis/download', [App\Http\Controllers\CCUAgingController::class, 'downloadCsv'])->name('ccu.aging.download');
Route::get('/aging/export', [App\Http\Controllers\CCUAgingController::class, 'export'])->name('aging.export');
Route::get('/ccu/export', [CCUController::class, 'export'])->name('ccu.export');


});


// Export current page (optional)
Route::get('/ccu/export-page', [CCUController::class, 'exportCsvPage'])->name('ccu.exportCsvPage');

// Export all data (memory safe, chunked)
Route::get('/ccu/export-all', [CCUController::class, 'exportCsvAll'])->name('ccu.exportCsvAll');


Route::get('/password/change', [UserProfileController::class, 'showPasswordChange'])->name('password.change');
Route::post('/password/change', [UserProfileController::class, 'updatePassword'])->name('password.update');


// use App\Http\Controllers\UserProfileController;

Route::middleware(['auth'])->group(function () {
    Route::get('/users/create', [UserProfileController::class, 'create'])->name('users.create');
    Route::post('/users', [UserProfileController::class, 'store'])->name('users.store');
});


// // Route::middleware(['auth'])->group(function() {
//   Route::get('staff', [UserProfileController::class, 'index'])->name('staff.profile');
//     Route::get('staff/profile', [UserProfileController::class, 'update'])->name('staff.profile.update');

// Route::post('staff/store', [UserProfileController::class, 'store'])->name('staff.store');

//     Route::get('staff/change-password', [UserProfileController::class, 'showChangePasswordForm'])->name('staff.change-password');
// Route::post('staff/change-password', [UserProfileController::class, 'updatePassword'])->name('staff.change-password.update');


// Route::get('password/change', [UserProfileController::class, 'showFirstLoginChangePasswordForm'])
//     ->name('password.change.form');

// Route::post('password/change', [UserProfileController::class, 'updateFirstLoginPassword'])
//     ->name('password.update.firstlogin');


//   Route::get('profile/{id}/edit', [UserProfileController::class, 'edit'])->name('staff.edit');
//     Route::put('profile/{id}', [UserProfileController::class, 'update'])->name('staff.update');
//     Route::delete('profile/{id}', [UserProfileController::class, 'destroy'])->name('staff.destroy');

    // Force password reset for a staff
    Route::post('profile/{id}/force-password-reset', [UserProfileController::class, 'forcePasswordReset'])
        ->name('staff.force-password-reset');

    // Change password
    Route::get('change-password/{id}', [UserProfileController::class, 'showChangePassword'])
        ->name('staff.change-password');
    Route::post('change-password/{id}', [UserProfileController::class, 'changePassword'])
        ->name('staff.change-password.update');

use App\Http\Controllers\Auth\StaffLoginController;

    Route::get('/staff', [StaffLoginController::class, 'showLoginForm'])->name('staff.login.form');
    Route::post('/staff/login', [StaffLoginController::class, 'login'])->name('staff.login.submit');
Route::post('/staff/logout', [StaffLoginController::class, 'logout'])->name('staff.logout');

// Route::prefix('staff')->group(function () {
//     Route::get('profile', [StaffLoginController::class, 'index'])->name('staff.profile');
//     Route::post('profile', [StaffLoginController::class, 'store'])->name('staff.store'); // <--- add this
// });
use App\Http\Controllers\StaffController;

// Route::prefix('staff')->group(function() {
//     Route::get('profile', [StaffLoginController::class, 'index'])->name('staff.profile');
//     // Route::post('profile', [StaffLoginController::class, 'store']);
//     Route::get('profile/{id}/edit', [StaffLoginController::class, 'edit']);
//     Route::put('profile/{id}', [StaffLoginController::class, 'update']);
//     Route::delete('profile/{id}', [StaffLoginController::class, 'destroy']);
//     Route::post('profile/{id}/force-password-reset', [StaffLoginController::class, 'forcePasswordReset']);
//     Route::get('change-password/{id}', [StaffLoginController::class, 'showChangePassword'])->name('staff.change-password');
//     Route::post('change-password/{id}', [StaffLoginController::class, 'changePassword']);
// });




Route::get('/login/{role}', [RoleLoginController::class, 'showLoginForm'])->name('role.login.form');
Route::post('/login/{role}', [RoleLoginController::class, 'login'])->name('role.login');
Route::post('/logout', [RoleLoginController::class, 'logout'])->name('role.logout');


Route::middleware(['auth'])->group(function() {
    Route::get('/staff/change-password', [StaffPasswordController::class, 'showChangeForm'])->name('staff.password.change');
    Route::post('/staff/change-password', [StaffPasswordController::class, 'updatePassword']);
});



Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/approve/{id}', [AdminController::class, 'approve'])->name('admin.approve');
    Route::post('/admin/reject/{id}', [AdminController::class, 'reject'])->name('admin.reject');
});


use App\Http\Controllers\Auth\RegisterController;

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

Route::get('/customer-login', [CustomerController::class, 'showLoginForm'])->name('customer.login.form');
Route::post('/customer-login', [CustomerController::class, 'login'])->name('customer.login');

Route::get('/customer-dashboard', [CustomerController::class, 'dashboard'])->name('customer.dashboard');

Route::post('/nin/validate', [CustomerController::class, 'ajaxValidateNIN'])->name('nin.validate');


Route::prefix('ccu')->group(function() {
    Route::get('/dashboard', [CCUDashboardController::class, 'index'])->name('ccu.dashboard');
    Route::get('/dashboard-stage/{stage}', [CCUDashboardController::class, 'getStageData'])->name('ccu.dashboard.stage');
});


Route::get('/send-bulk', [BulkMailController::class, 'sendBulk']);





Route::get('/customer/logout', function () {
    session()->forget(['customer', 'account_type']);
    return redirect()->route('customer.login.form')->with('success', 'Logged out successfully.');
})->name('customer.logout');


Route::post('/staff', [StaffLoginController::class, 'store'])->name('staff.login.submit');


Route::prefix('staff')->name('staff.')->group(function () {

    // Login routes
    Route::get('/login', [StaffLoginController::class, 'showLoginForm'])->name('login.form');
    // Route::post('/login', [StaffLoginController::class, 'store'])->name('login');

    // Logout
    Route::post('/logout', [StaffLoginController::class, 'logout'])->name('logout');

    // Password change
    Route::get('/change-password/{id}', [StaffLoginController::class, 'showChangePasswordForm'])
        ->name('change-password');
    Route::post('/change-password/{id}', [StaffLoginController::class, 'updatePassword'])
        ->name('update-password');
});

// routes/web.php
// Route::post('/landlord/submit', [CustomerController::class, 'submitLandlordForm'])->name('customer.update.landlord.submit');
Route::post('/validate-nin', [CustomerController::class, 'validateNin'])->name('validate.nin');


Route::get('/customer/update/select', [CustomerController::class, 'showSelectFieldsForm'])->name('customer.update.occupancy.select');
Route::post('/customer/update/select', [CustomerController::class, 'handleSelectFields'])->name('customer.update.occupancy.handle');




Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users/bulk-upload', [UserController::class, 'bulkUpload'])->name('users.bulkUpload');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');


// Step 2: Landlord and Tenant Forms
Route::get('/customer/update/landlord', [CustomerController::class, 'showLandlordForm'])->name('customer.update.landlord.form');
Route::post('/customer/update/landlord', [CustomerController::class, 'submitLandlordForm'])->name('customer.update.landlord.submit');

Route::get('/customer/update/tenant', [CustomerController::class, 'showTenantForm'])->name('customer.update.tenant.form');
Route::post('/customer/update/tenant', [CustomerController::class, 'submitTenantForm'])->name('customer.update.tenant.submit');


Route::middleware(['auth', 'role:billing'])->group(function () {
    Route::get('/billing/dashboard', [BillingController::class, 'dashboard'])->name('billing.dashboard');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Routes (web.php)
    Route::get('/dashboard/rkam', [ValidationController::class, 'showRKAMDashboard'])->name('rkam.dashboard');
    Route::get('/dashboard/bm', [ValidationController::class, 'showBMDashboard'])->name('bm.dashboard');


Route::get('/test-mail', function () {
    Mail::raw('Test email from IBEDC KYC!', function ($message) {
        $message->to('al-ameen.ameen@ibedc.com') // Swap your email
                ->subject('KYC Mail Test');
    });
    return 'Mail sent—check inbox!';
});

    // Customer Care Login
    Route::get('/customercare/login', [StaffLoginController::class, 'showLoginForm'])->name('customercare.login.form');
    Route::post('/customercare/login', [StaffLoginController::class, 'login'])->name('customercare.login.submit');

// Route::middleware(['auth', 'role:ccu'])->prefix('ccu')->group(function () {
//     Route::get('/dashboard', [CCUDashboardController::class, 'index'])->name('ccu.dashboard');
//     Route::get('/view/{id}', [CCUDashboardController::class, 'view'])->name('ccu.view');
// });


    // Customer Care Dashboard & KYC Input
    Route::middleware('auth')->group(function () {
     
        Route::post('/customercare/logout', [StaffLoginController::class, 'logout'])->name('customercare.logout');
    });


    // ... billing approval routes
});


Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/billing/dashboard', [BillingController::class, 'dashboard'])->name('billing.dashboard');
    // Route::get('/rico/dashboard', [RicoController::class, 'dashboard'])->name('rico.dashboard');
    Route::get('/audit/dashboard', [AuditController::class, 'dashboard'])->name('audit.dashboard');
    Route::get('/rkam/dashboard', [RKAMController::class, 'index'])->name('rkam.dashboard');
    Route::get('/rkam/reports', [App\Http\Controllers\RKAMController::class, 'showReports'])->name('rkam.reports');
Route::get('/rkam/reports', [RKAMController::class, 'showReports'])->name('rkam.reports');
Route::get('/rkam/reports/approved', [RKAMController::class, 'showApproved'])->name('rkam.reports.approved');
Route::get('/rkam/reports/rejected', [RKAMController::class, 'showRejected'])->name('rkam.reports.rejected');

Route::get('/billing/reports', [App\Http\Controllers\BillingController::class, 'showReports'])->name('billing.reports');
Route::get('/billing/reports', [BillingController::class, 'showReports'])->name('billing.reports');
Route::get('/billing/reports/approved', [BillingController::class, 'showApproved'])->name('billing.reports.approved');
Route::get('/billing/reports/rejected', [BillingController::class, 'showRejected'])->name('billing.reports.rejected');


    Route::get('/rico/reports', [App\Http\Controllers\ricocontroller::class, 'showReports'])->name('rico.reports');
Route::get('/rico/reports', [ricocontroller::class, 'showReports'])->name('rico.reports');
Route::get('/rico/reports/approved', [ricocontroller::class, 'showApproved'])->name('rico.reports.approved');
Route::get('/rico/reports/rejected', [ricocontroller::class, 'showRejected'])->name('rico.reports.rejected');




Route::get('/test-mail', function () {
    Mail::raw('This is a test email from Laravel using IBEDC SMTP', function ($message) {
        $message->to('al.ameenmemmcol@gmail.com')
                ->subject('Test Email');
    });
    return 'Mail sent successfully!';
});


//   Route::get('/bm/reports', [App\Http\Controllers\BMController::class, 'showReports'])->name('bm.reports');
Route::get('/bm/reports', [BMController::class, 'showReports'])->name('bm.reports');
Route::get('/bm/reports/approved', [BMController::class, 'showApproved'])->name('bm.reports.approved');
Route::get('/bm/reports/rejected', [BMController::class, 'showRejected'])->name('bm.reports.rejected');

    Route::post('/rkam/approve/{id}', [RKAMController::class, 'approve'])->name('rkam.approve');
    Route::post('/rkam/reject/{id}', [RKAMController::class, 'reject'])->name('rkam.reject');
    Route::get('/bm/dashboard', [BMController::class, 'index'])->name('bm.dashboard');
    // Route::post('/bm/approve/{id}', [BMController::class, 'approve'])->name('bm.approve');
    // Route::post('/bm/reject/{id}', [BMController::class, 'reject'])->name('bm.reject');
    // Route::post('/bm/bulk-approve', [BMController::class, 'bulkApprove'])->name('bm.bulk.approve');
// Route::post('/bm/bulk-reject', [BMController::class, 'bulkReject'])->name('bm.bulk.reject');

Route::get('/ccu/dashboard2', [CCUController2::class, 'dashboard'])->name('ccu.dashboard2');

Route::post('/bm/approve/{id}', [BMController::class, 'approve'])->name('bm.approve');
Route::post('/bm/reject/{id}', [BMController::class, 'reject'])->name('bm.reject');

// ✅ This is the missing route your AJAX calls depend on
Route::post('/bm/update/{id}', [BMController::class, 'updateStatus'])->name('bm.update');

Route::post('/bm/bulk-approve', [BMController::class, 'bulkApprove'])->name('bm.bulk.approve');
Route::post('/bm/bulk-reject', [BMController::class, 'bulkReject'])->name('bm.bulk.reject');


    // Route::get('/admin/audit-logs', [AdminController::class, 'showAuditLogs'])->name('admin.audit.logs');


   Route::post('/rico/bulk-approve', [ricocontroller::class, 'bulkApprove'])->name('rico.bulk.approve');
Route::post('/rico/bulk-reject', [ricocontroller::class, 'bulkReject'])->name('rico.bulk.reject');

  Route::post('/billing/bulk-approve', [billingcontroller::class, 'bulkApprove'])->name('billing.bulk.approve');
Route::post('/billing/bulk-reject', [billingcontroller::class, 'bulkReject'])->name('billing.bulk.reject');

Route::post('/rkam/bulk-approve', [RKAMController::class, 'bulkApprove'])->name('rkam.bulkApprove');
Route::post('/rkam/bulk-reject', [RKAMController::class, 'bulkReject'])->name('rkam.bulkReject');

// Route::post('/rico/bulk-approve', [RicoController::class, 'bulkApprove'])->name('rico.bulkApprove');
// Route::post('/rico/bulk-reject', [RKAMController::class, 'bulkReject'])->name('rkam.bulkReject');


//  Route::post('/approve/{id}', [RicoController::class, 'approve'])->name('rico.approve');
//     Route::post('/reject/{id}', [RicoController::class, 'reject'])->name('rico.reject');

//     // Bulk actions
//     Route::post('/bulk-approve', [RicoController::class, 'bulkApprove'])->name('rico.bulkApprove');
//     Route::post('/bulk-reject', [RicoController::class, 'bulkReject'])->name('rico.bulkReject');


//    Route::post('/rico/{id}/approve', [RicoController::class, 'approve'])->name('rico.approve');
// Route::post('/rico/{id}/reject', [RicoController::class, 'reject'])->name('rico.reject');

// Route::post('/rico/bulk-approve', [RicoController::class, 'bulkApprove'])->name('rico.bulkApprove');
// Route::post('/rico/bulk-reject', [RicoController::class, 'bulkReject'])->name('rico.bulkReject');

    // web.php
Route::post('/bm/bulk-update', [BMController::class, 'bulkUpdate'])->name('bm.bulkUpdate');


});

// Route::middleware(['auth', 'ensureuserisBilling'])->group(function () {
Route::middleware(['auth'])->group(function () {

    Route::get('billing/dashboard', [BillingController::class, 'dashboard'])->name('billing.dashboard');
    Route::post('billing/approve/{id}', [BillingController::class, 'approve'])->name('billing.approve');
    Route::post('billing/reject/{id}', [BillingController::class, 'reject'])->name('billing.reject');

 Route::post('/billing/bulk-approve', [BillingController::class, 'bulkApprove'])->name('billing.bulk.approve');
Route::post('/billing/bulk-reject', [BillingController::class, 'bulkReject'])->name('billing.bulk.reject');
Route::post('/billing/bulk-action', [BillingController::class, 'bulkAction'])->name('billing.bulkAction');

});

Route::get('/ccu/{id}', [CCUController2::class,'show'])->name('ccu.show');



Route::middleware(['auth'])->group(function () {
    Route::get('/audit/dashboard', [AuditController::class, 'dashboard'])->name('audit.dashboard');
    // ... audit approval routes
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    // ... admin approval routes
});


// Route::middleware(['auth', 'role:rico'])->group(function () {
 Route::middleware(['auth'])->group(function () {
    // Route::middleware(['auth', 'checkrole:rico'])->group(function () {
    Route::get('/rico/dashboard', [RicoController::class, 'dashboard'])->name('rico.dashboard');
    Route::post('/rico/approve/{id}', [RicoController::class, 'approve'])->name('rico.approve');
    Route::post('/rico/reject/{id}', [RicoController::class, 'reject'])->name('rico.reject');
    Route::get('/view-document/{path}', [RicoController::class, 'viewDocument'])->name('rico.view.document');

});

Route::get('/ccu/export-csv', [CCUController::class, 'exportCsv'])->name('ccu.export.csv');


Route::middleware(['auth'])->prefix('ccu')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\CCUController::class, 'dashboard'])->name('ccu.dashboard');
    Route::get('/details/{id}', [App\Http\Controllers\CCUController::class, 'showDetails'])->name('ccu.details');
});


Route::get('/redirect-based-on-role', function () {
    $user = auth()->user();

    return match ($user->role) {
        'rico' => redirect()->route('rico.dashboard'),
        'billing' => redirect()->route('billing.dashboard'),
        'audit' => redirect()->route('audit.dashboard'),
        'admin' => redirect()->route('admin.dashboard'),
        default => abort(403, 'Role not defined.')
    };
})->middleware(['auth']);


// Route::get('/', function () {
//     return response()->json(['message' => 'KYC API']);
// });


Route::get('/', function () {
    return redirect()->route('customer.login');
});



Route::get('/send-test-email', [TestMailController::class, 'sendTestEmail']);





Route::middleware(['auth', 'ensureAdmin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminViewController::class, 'dashboard'])->name('admin.dashboard');
});

Route::get('/test-email', function () {
    $data = ['message' => 'This is a test email!'];

    Mail::send([], $data, function ($message) {
        $message->to('al-ameen.ameen@ibedc.com')
            ->subject('Test Email')
            ->setBody('This is a test email from Laravel');
    });

    return 'Email sent!';
});


Route::get('/test-db', function () {
    // Test .250 database (default connection)
    $result250 = DB::select('SELECT 1');

    // Test .89 database connection
    $result89 = DB::connection('sqlsrv89')->select('SELECT 1');

    // Return results to verify both connections
    return response()->json([
        'result250' => $result250,
        'result89' => $result89,
    ]);

});




